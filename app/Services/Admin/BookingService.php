<?php

namespace App\Services\Admin;

use App\Models\Booking;
use App\Models\Destination;
use App\Services\BrevoMailService;
use App\Services\NotificationService;
use App\Services\TicketService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class BookingService
{
    public function __construct(
        protected TicketService $ticketService,
        protected BrevoMailService $mailService,
        protected NotificationService $notificationService
    ) {
    }

    /**
     * Calculate booking prices based on destination and quantities
     */
    public function calculatePrices(Destination $destination, array $data): array
    {
        $prices = $destination->prices->keyBy('category');
        $subtotal = 0;
        $items = [];

        $addItem = function ($category, $qty) use ($prices, &$subtotal, &$items) {
            if ($qty > 0 && isset($prices[$category])) {
                $itemTotal = $prices[$category]->price * $qty;
                $items[] = [
                    'destination_price_id' => $prices[$category]->id,
                    'category' => $category,
                    'label' => $prices[$category]->label ?? ucfirst($category),
                    'quantity' => $qty,
                    'unit_price' => $prices[$category]->price,
                    'total_price' => $itemTotal,
                ];
                $subtotal += $itemTotal;
            }
        };

        $addItem('adult', $data['total_adults'] ?? 0);
        $addItem('child', $data['total_children'] ?? 0);
        $addItem('senior', $data['total_seniors'] ?? 0);
        $addItem('motor', $data['total_motorcycles'] ?? 0);
        $addItem('car', $data['total_cars'] ?? 0);
        $addItem('bus', $data['total_buses'] ?? 0);

        $serviceFee = 5000;
        $totalAmount = $subtotal + $serviceFee;

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Create a new booking with all related items
     */
    public function createBooking(array $validated, Destination $destination): Booking
    {
        $totalVisitors = ($validated['total_adults'] ?? 0) + ($validated['total_children'] ?? 0) + ($validated['total_seniors'] ?? 0);
        $pricing = $this->calculatePrices($destination, $validated);

        $booking = Booking::create([
            'order_number' => Booking::generateOrderNumber(),
            'user_id' => $validated['user_id'] ?? null,
            'destination_id' => $destination->id,
            'visit_date' => $validated['visit_date'],
            'leader_name' => $validated['leader_name'],
            'leader_phone' => $validated['leader_phone'],
            'leader_email' => $validated['leader_email'],
            'total_visitors' => $totalVisitors,
            'total_adults' => $validated['total_adults'],
            'total_children' => $validated['total_children'] ?? 0,
            'total_seniors' => $validated['total_seniors'] ?? 0,
            'total_motorcycles' => $validated['total_motorcycles'] ?? 0,
            'total_cars' => $validated['total_cars'] ?? 0,
            'total_buses' => $validated['total_buses'] ?? 0,
            'subtotal' => $pricing['subtotal'],
            'service_fee' => $pricing['service_fee'],
            'total_amount' => $pricing['total_amount'],
            'status' => $validated['status'],
            'confirmed_at' => $validated['status'] === 'confirmed' ? now() : null,
        ]);

        // Create booking items
        foreach ($pricing['items'] as $item) {
            $booking->items()->create($item);
        }

        return $booking;
    }

    /**
     * Process payment and tickets for a booking
     */
    public function processPaymentAndTickets(Booking $booking, string $paymentType = 'cash'): void
    {
        // Create payment record
        $booking->payments()->create([
            'order_id' => $booking->order_number,
            'payment_type' => $paymentType,
            'gross_amount' => $booking->total_amount,
            'status' => 'success',
            'paid_at' => now(),
        ]);

        // Generate tickets
        $this->ticketService->generateTicketsForBooking($booking);

        // Send confirmation email
        $this->sendConfirmationEmail($booking);
    }

    /**
     * Send confirmation email with ticket PDF
     */
    public function sendConfirmationEmail(Booking $booking): bool
    {
        try {
            Log::info('Starting to send confirmation email for booking: ' . $booking->order_number);

            $booking->load(['destination', 'tickets']);
            $pdfContent = Pdf::loadView('pdf.ticket', compact('booking'))->output();

            Log::info('PDF generated successfully for booking: ' . $booking->order_number);

            $result = $this->mailService->sendBookingConfirmation($booking, $pdfContent);

            Log::info('Email send result for booking ' . $booking->order_number . ': ' . ($result ? 'success' : 'failed'));

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to send booking confirmation email: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Update booking and handle status change
     */
    public function updateBooking(Booking $booking, array $validated): void
    {
        $oldStatus = $booking->status;

        $booking->update([
            'visit_date' => $validated['visit_date'],
            'leader_name' => $validated['leader_name'],
            'leader_phone' => $validated['leader_phone'],
            'leader_email' => $validated['leader_email'],
            'status' => $validated['status'],
        ]);

        // Status changed to Confirmed from Pending
        if (
            $validated['status'] === 'confirmed' &&
            $oldStatus !== 'confirmed' &&
            $booking->tickets->isEmpty()
        ) {

            $this->processPaymentAndTickets($booking, 'cash');
        }

        // Update ticket validity if visit date changed
        if ($booking->wasChanged('visit_date')) {
            $booking->tickets()->update(['valid_date' => $validated['visit_date']]);
        }
    }

    /**
     * Cancel a booking
     */
    public function cancelBooking(Booking $booking): void
    {
        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Invalidate all tickets
        $booking->tickets()->update(['status' => 'cancelled']);

        // Notify user if exists
        if ($booking->user_id) {
            $this->notificationService->createNotification(
                $booking->user_id,
                'booking_cancelled',
                'Booking Dibatalkan',
                "Booking #{$booking->order_number} telah dibatalkan oleh admin."
            );
        }
    }
}
