<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService
{
    protected NotificationService $notificationService;
    protected BrevoMailService $mailService;
    protected TicketService $ticketService;

    public function __construct(
        NotificationService $notificationService,
        BrevoMailService $mailService,
        TicketService $ticketService
    ) {
        $this->notificationService = $notificationService;
        $this->mailService = $mailService;
        $this->ticketService = $ticketService;
    }

    /**
     * Confirm cash payment - booking will be paid at counter
     */
    public function confirmCashPayment(Booking $booking): Booking
    {
        if ($booking->status !== Booking::STATUS_PENDING) {
            return $booking;
        }

        // Calculate expiry: 24 hours from now OR 1 day before visit date, whichever is earlier
        $expiryFromNow = now()->addHours(24);
        $expiryBeforeVisit = $booking->visit_date->subDay();
        $expiredAt = $expiryFromNow->lt($expiryBeforeVisit) ? $expiryFromNow : $expiryBeforeVisit;

        // Update booking status to awaiting cash payment
        $booking->update([
            'status' => 'awaiting_cash',
            'expired_at' => $expiredAt,
        ]);

        // Create or update payment record for cash
        $booking->payments()->updateOrCreate(
            ['order_id' => $booking->order_number],
            [
                'payment_type' => 'cash',
                'payment_channel' => 'counter',
                'gross_amount' => $booking->total_amount,
                'status' => 'pending',
                'expired_at' => $expiredAt,
            ]
        );

        // Send notifications
        $this->notificationService->notifyCashPaymentConfirmed($booking);
        $this->mailService->sendCashPaymentInstructions($booking);

        return $booking->fresh();
    }

    /**
     * Auto-heal booking: Check Midtrans status and generate missing tickets
     */
    public function autoHealBooking(Booking $booking, MidtransService $midtransService): Booking
    {
        // 1. Auto-check Midtrans for Pending bookings
        if ($booking->status === Booking::STATUS_PENDING) {
            $booking = $this->checkMidtransStatus($booking, $midtransService);
        }

        // 2. Auto-heal: If Paid but Ticket/Email missing
        if ($booking->status === Booking::STATUS_PAID && !$booking->ticket) {
            $booking = $this->generateMissingTickets($booking);
        }

        return $booking;
    }

    /**
     * Check Midtrans status for pending booking
     */
    protected function checkMidtransStatus(Booking $booking, MidtransService $midtransService): Booking
    {
        try {
            $status = $midtransService->getStatus($booking->order_number);
            if ($status && in_array($status['transaction_status'], ['settlement', 'capture'])) {
                $booking->update(['status' => Booking::STATUS_PAID]);

                // Update/Create Payment
                $payment = $booking->payment;
                if (!$payment) {
                    $booking->payments()->create([
                        'payment_type' => $status['payment_type'] ?? 'unknown',
                        'gross_amount' => $status['gross_amount'] ?? $booking->total_amount,
                        'status' => Payment::STATUS_SUCCESS,
                        'paid_at' => now(),
                        'midtrans_response' => json_encode($status)
                    ]);
                } else {
                    $payment->update([
                        'status' => Payment::STATUS_SUCCESS,
                        'paid_at' => now(),
                        'midtrans_response' => json_encode($status)
                    ]);
                }

                $booking->refresh();
            }
        } catch (\Exception $e) {
            Log::warning('Auto-check midtrans failed: ' . $e->getMessage());
        }

        return $booking;
    }

    /**
     * Generate missing tickets for paid booking
     */
    protected function generateMissingTickets(Booking $booking): Booking
    {
        try {
            // Generate Ticket
            $this->ticketService->generateTicketsForBooking($booking);

            // Send Email
            $pdfContent = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.ticket', compact('booking'))->output();
            $this->mailService->sendBookingConfirmation($booking, $pdfContent);

            // Update Payment Status if needed
            if ($booking->payment && $booking->payment->status !== Payment::STATUS_SUCCESS) {
                $booking->payment->update([
                    'status' => Payment::STATUS_SUCCESS,
                    'paid_at' => now()
                ]);
            }

            $booking->refresh();
        } catch (\Exception $e) {
            Log::error('Booking auto-heal failed: ' . $e->getMessage());
        }

        return $booking;
    }

    /**
     * Calculate booking items from quantities and prices
     */
    public function calculateBookingItems(array $quantities, Collection $prices): array
    {
        $items = [];
        $subtotal = 0;
        $totalVisitors = 0;
        $summary = [
            'total_adults' => 0,
            'total_children' => 0,
            'total_seniors' => 0,
            'total_motorcycles' => 0,
            'total_cars' => 0,
            'total_buses' => 0,
        ];

        $pricesById = $prices->keyBy('id');

        foreach ($quantities as $priceId => $qty) {
            if ($qty > 0 && isset($pricesById[$priceId])) {
                $price = $pricesById[$priceId];

                // Add to items
                $itemTotal = $price->price * $qty;
                $items[] = [
                    'destination_price_id' => $price->id,
                    'category' => $price->category,
                    'label' => $price->label,
                    'quantity' => $qty,
                    'unit_price' => $price->price,
                    'total_price' => $itemTotal,
                ];
                $subtotal += $itemTotal;

                // Update summary counters
                $this->updateSummaryCounters($summary, $price, $qty);

                // Count visitors (exclude vehicles)
                if (!$price->isVehicleCategory()) {
                    $totalVisitors += $qty;
                }
            }
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'total_visitors' => $totalVisitors,
            'summary' => $summary,
        ];
    }

    /**
     * Update summary counters based on category
     */
    protected function updateSummaryCounters(array &$summary, $price, int $qty): void
    {
        if ($price->category === 'dewasa' || $price->category === 'adult') {
            $summary['total_adults'] += $qty;
        } elseif ($price->category === 'anak' || $price->category === 'child') {
            $summary['total_children'] += $qty;
        } elseif ($price->category === 'lansia' || $price->category === 'senior') {
            $summary['total_seniors'] += $qty;
        } elseif (str_contains($price->category, 'motor')) {
            $summary['total_motorcycles'] += $qty;
        } elseif (str_contains($price->category, 'mobil') || str_contains($price->category, 'car')) {
            $summary['total_cars'] += $qty;
        } elseif (str_contains($price->category, 'bus')) {
            $summary['total_buses'] += $qty;
        }
    }

    /**
     * Apply coupon and calculate discount
     */
    public function applyCoupon(?string $code, float $grossTotal, int $destinationId): array
    {
        if (empty($code)) {
            return ['discount' => 0, 'discount_code' => null];
        }

        $coupon = \App\Models\Coupon::findByCode($code);
        if (!$coupon) {
            return ['discount' => 0, 'discount_code' => null];
        }

        $validation = $coupon->isValid(auth()->id(), $grossTotal, $destinationId);
        if (!$validation['valid']) {
            return ['discount' => 0, 'discount_code' => null];
        }

        return [
            'discount' => $coupon->calculateDiscount($grossTotal),
            'discount_code' => $coupon->code,
        ];
    }
}
