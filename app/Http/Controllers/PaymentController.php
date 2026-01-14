<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\MidtransService;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $midtransService;
    protected $ticketService;
    protected $mailService;

    public function __construct(
        MidtransService $midtransService,
        TicketService $ticketService,
        \App\Services\BrevoMailService $mailService
    ) {
        $this->midtransService = $midtransService;
        $this->ticketService = $ticketService;
        $this->mailService = $mailService;
    }

    /**
     * Handle Midtrans notification callback.
     */
    public function handleNotification(Request $request)
    {
        try {
            $notification = $this->midtransService->handleNotification();

            if (!$notification) {
                return response()->json(['status' => 'error', 'message' => 'Invalid notification'], 400);
            }

            $orderId = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status ?? null;
            $paymentType = $notification->payment_type;

            // Find booking by order number
            $booking = Booking::where('order_number', $orderId)->first();

            if (!$booking) {
                Log::warning('Midtrans notification: booking not found', ['order_id' => $orderId]);
                return response()->json(['status' => 'error', 'message' => 'Booking not found'], 404);
            }

            // Find or create payment record
            $payment = Payment::firstOrNew(['booking_id' => $booking->id]);
            $payment->transaction_id = $notification->transaction_id ?? null;
            $payment->payment_type = $paymentType;
            $payment->amount = $notification->gross_amount;
            $payment->midtrans_response = json_encode($notification);

            // Handle transaction status
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    $payment->status = Payment::STATUS_SUCCESS;
                    $payment->paid_at = now();
                    $this->handleSuccessfulPayment($booking);
                }
            } elseif ($transactionStatus == 'settlement') {
                $payment->status = Payment::STATUS_SUCCESS;
                $payment->paid_at = now();
                $this->handleSuccessfulPayment($booking);
            } elseif ($transactionStatus == 'pending') {
                $payment->status = Payment::STATUS_PENDING;
            } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                $payment->status = Payment::STATUS_FAILED;
                $booking->update(['status' => Booking::STATUS_CANCELLED]);
            }

            $payment->save();

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Payment notification error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle successful payment - update booking and generate tickets.
     */
    protected function handleSuccessfulPayment(Booking $booking)
    {
        $booking->update(['status' => Booking::STATUS_CONFIRMED]);

        // Record coupon usage if coupon was used
        if ($booking->discount_code && $booking->user_id) {
            try {
                $coupon = \App\Models\Coupon::findByCode($booking->discount_code);
                if ($coupon) {
                    $coupon->markAsUsed($booking->user_id, $booking->id);
                    Log::info("Coupon {$coupon->code} marked as used for booking {$booking->order_number}");
                }
            } catch (\Exception $e) {
                Log::error('Failed to mark coupon as used: ' . $e->getMessage());
            }
        }

        // Generate tickets
        try {
            $this->ticketService->generateTicketsForBooking($booking);
            Log::info("Tickets generated for booking {$booking->order_number}");
        } catch (\Exception $e) {
            Log::error('Failed to generate tickets: ' . $e->getMessage());
        }

        // Send Email Confirmation with PDF
        try {
            $pdfContent = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.ticket', compact('booking'))->output();
            $emailSent = $this->mailService->sendBookingConfirmation($booking, $pdfContent);

            if ($emailSent) {
                Log::info("Confirmation email sent to {$booking->leader_email} for booking {$booking->order_number}");
            } else {
                Log::warning("Failed to send confirmation email to {$booking->leader_email} for booking {$booking->order_number} (Service returned false)");
            }
        } catch (\Exception $e) {
            Log::error('Failed to send email confirmation: ' . $e->getMessage());
        }

        // Send In-App Notification
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyPaymentSuccess($booking);
            Log::info("Payment success notification sent for booking {$booking->order_number}");
        } catch (\Exception $e) {
            Log::error('Failed to send payment success notification: ' . $e->getMessage());
        }
    }

    /**
     * Finish page after payment (redirect from Midtrans).
     */
    public function finish(Request $request)
    {
        $orderId = $request->get('order_id');

        if ($orderId) {
            // Proactively check status from Midtrans since callback might fail locally
            $this->checkStatusFromMidtrans($orderId);

            return redirect()->route('booking.success', $orderId);
        }

        return redirect()->route('home');
    }

    /**
     * Unfinish page (pending payment).
     */
    public function unfinish(Request $request)
    {
        $orderId = $request->get('order_id');

        if ($orderId) {
            $this->checkStatusFromMidtrans($orderId);

            return redirect()->route('booking.payment', $orderId)
                ->with('warning', 'Pembayaran belum selesai. Silakan selesaikan pembayaran Anda.');
        }

        return redirect()->route('home');
    }

    /**
     * Check payment status via AJAX.
     */
    public function checkStatus($orderNumber)
    {
        // Always try to sync with Midtrans first
        $this->checkStatusFromMidtrans($orderNumber);

        $booking = Booking::where('order_number', $orderNumber)->firstOrFail();

        return response()->json([
            'status' => $booking->status,
            'is_paid' => $booking->status === Booking::STATUS_CONFIRMED,
        ]);
    }

    /**
     * Helper to check status from Midtrans and update DB
     */
    protected function checkStatusFromMidtrans($orderId)
    {
        try {
            $status = $this->midtransService->getStatus($orderId);
            $booking = Booking::where('order_number', $orderId)->first();

            if ($status && $booking) {
                $transactionStatus = $status['transaction_status'];
                $fraudStatus = $status['fraud_status'] ?? null;
                $paymentType = $status['payment_type'] ?? null;

                // Find or create payment record
                $payment = Payment::firstOrNew(['booking_id' => $booking->id]);
                if (!$payment->exists) {
                    $payment->gross_amount = $status['gross_amount'] ?? $booking->total_amount;
                }

                // Update payment fields if not set
                if (empty($payment->payment_type) && $paymentType) {
                    $payment->payment_type = $paymentType;
                }
                if (empty($payment->transaction_id) && isset($status['transaction_id'])) {
                    $payment->transaction_id = $status['transaction_id'];
                }

                // Determine new status
                $newStatus = null;
                if ($transactionStatus == 'capture') {
                    if ($fraudStatus == 'accept') {
                        $newStatus = Payment::STATUS_SUCCESS;
                    }
                } elseif ($transactionStatus == 'settlement') {
                    $newStatus = Payment::STATUS_SUCCESS;
                } elseif ($transactionStatus == 'pending') {
                    $newStatus = Payment::STATUS_PENDING;
                } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                    $newStatus = Payment::STATUS_FAILED;
                }

                // Only act if status changed or wasn't set correctly
                if ($newStatus && $payment->status !== $newStatus) {
                    $payment->status = $newStatus;
                    $payment->midtrans_response = json_encode($status);

                    if ($newStatus === Payment::STATUS_SUCCESS) {
                        $payment->paid_at = now();

                        // Check if booking needs processing (not paid OR tickets missing due to error)
                        // Load ticket relationship to be sure
                        $booking->load('ticket');

                        if ($booking->status !== Booking::STATUS_CONFIRMED || !$booking->ticket) {
                            $this->handleSuccessfulPayment($booking);

                            // Manually trigger notification if not yet triggered (rare case if status was already paid)
                            if ($booking->status === Booking::STATUS_CONFIRMED) {
                                // If we are here, it means status was PAID but ticket missing.
                                // handleSuccessfulPayment generates ticket.
                                // We might want to resend success notification too just in case.
                            } else {
                                // Normal flow: Status changed to PAID.
                                // Notification handled by handleSuccessfulPayment? No, we removed it from there?
                                // Let's check handleSuccessfulPayment code in step 698.
                                // It generates tickets and sends Email. It does NOT send In-App Notification (NotificationService).
                                // NotificationService was typically triggered via MidtransService::handleNotification.
                                // But since we are bypassing MidtransService's notification logic here (we are in checkStatusFromMidtrans),
                                // we should trigger In-App Notification here if status changed.

                                $notificationService = app(\App\Services\NotificationService::class);
                                $notificationService->notifyPaymentSuccess($booking);
                            }
                        }
                    } elseif ($newStatus === Payment::STATUS_FAILED) {
                        $booking->update(['status' => Booking::STATUS_CANCELLED]);
                        $notificationService = app(\App\Services\NotificationService::class);
                        $notificationService->notifyPaymentFailed($booking, "Pembayaran $transactionStatus");
                    }

                    $payment->save();
                }
            }
        } catch (\Exception $e) {
            Log::error('Check status error: ' . $e->getMessage());
        }
    }
}
