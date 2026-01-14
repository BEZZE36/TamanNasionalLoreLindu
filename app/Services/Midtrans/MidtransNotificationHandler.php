<?php

namespace App\Services\Midtrans;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Midtrans\Notification;

class MidtransNotificationHandler
{
    protected NotificationService $notificationService;
    protected MidtransPaymentMapper $paymentMapper;

    public function __construct(
        NotificationService $notificationService,
        MidtransPaymentMapper $paymentMapper
    ) {
        $this->notificationService = $notificationService;
        $this->paymentMapper = $paymentMapper;
    }

    /**
     * Handle payment notification from Midtrans
     */
    public function handle(): array
    {
        try {
            $notification = new Notification();

            $orderId = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $paymentType = $notification->payment_type;
            $fraudStatus = $notification->fraud_status ?? null;

            // Find payment
            $payment = Payment::where('order_id', $orderId)->first();

            if (!$payment) {
                Log::warning('Payment not found for notification', ['order_id' => $orderId]);
                return ['success' => false, 'message' => 'Payment not found'];
            }

            // Update payment details
            $this->updatePaymentDetails($payment, $notification, $paymentType);

            // Determine and set new status
            $newStatus = $this->paymentMapper->determinePaymentStatus($transactionStatus, $fraudStatus);
            $payment->status = $newStatus;

            // Handle status-specific actions
            $this->handlePaymentStatusChange($payment, $newStatus);

            $payment->save();

            Log::info('Midtrans notification processed', [
                'order_id' => $orderId,
                'status' => $newStatus,
                'payment_type' => $paymentType,
            ]);

            return ['success' => true, 'payment' => $payment];
        } catch (\Exception $e) {
            Log::error('Midtrans notification error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Update payment details from notification
     */
    protected function updatePaymentDetails(Payment $payment, Notification $notification, string $paymentType): void
    {
        $payment->transaction_id = $notification->transaction_id;
        $payment->payment_type = $this->paymentMapper->mapPaymentType($paymentType);
        $payment->payment_channel = $notification->bank ?? $notification->store ?? $paymentType;
        $payment->notification_response = (array) $notification;

        // Get VA number or payment code
        if (isset($notification->va_numbers) && count($notification->va_numbers) > 0) {
            $payment->va_number = $notification->va_numbers[0]->va_number;
            $payment->payment_channel = $notification->va_numbers[0]->bank;
        }
        if (isset($notification->payment_code)) {
            $payment->payment_code = $notification->payment_code;
        }
        if (isset($notification->actions)) {
            foreach ($notification->actions as $action) {
                if ($action->name === 'generate-qr-code') {
                    $payment->qr_code_url = $action->url;
                }
            }
        }
    }

    /**
     * Handle payment status change and trigger appropriate actions
     */
    protected function handlePaymentStatusChange(Payment $payment, string $newStatus): void
    {
        if ($newStatus === Payment::STATUS_SUCCESS) {
            $this->handleSuccessfulPayment($payment);
        } elseif (in_array($newStatus, [Payment::STATUS_FAILED, Payment::STATUS_EXPIRED, Payment::STATUS_DENY])) {
            $this->handleFailedPayment($payment, $newStatus);
        } elseif ($newStatus === Payment::STATUS_PENDING) {
            $this->notificationService->notifyPaymentPending($payment->booking);
        }
    }

    /**
     * Handle successful payment
     */
    protected function handleSuccessfulPayment(Payment $payment): void
    {
        $payment->paid_at = now();

        $booking = $payment->booking;
        $booking->update([
            'status' => Booking::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);

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

        $this->notificationService->notifyPaymentSuccess($booking);
    }

    /**
     * Handle failed/expired/denied payment
     */
    protected function handleFailedPayment(Payment $payment, string $status): void
    {
        $payment->booking->update([
            'status' => Booking::STATUS_CANCELLED,
            'expired_at' => now(),
        ]);

        if ($status === Payment::STATUS_EXPIRED) {
            $this->notificationService->notifyPaymentExpired($payment->booking);
        } else {
            $this->notificationService->notifyPaymentFailed($payment->booking, 'Pembayaran ditolak atau gagal.');
        }
    }
}
