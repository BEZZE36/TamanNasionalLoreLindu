<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketScanService
{
    /**
     * Get today's scan statistics
     */
    public function getTodayStats(): array
    {
        return [
            'scans' => Ticket::where('status', 'used')
                ->whereDate('used_at', today())
                ->count(),
            'payments' => Payment::where('payment_type', 'cash')
                ->where('status', 'success')
                ->whereDate('paid_at', today())
                ->count(),
            'revenue' => Payment::where('payment_type', 'cash')
                ->where('status', 'success')
                ->whereDate('paid_at', today())
                ->sum('gross_amount'),
            // Tickets ready to be validated (valid tickets for today's visit date)
            'unused' => Ticket::where('status', 'valid')
                ->whereHas('booking', function ($q) {
                    $q->whereDate('visit_date', today())
                        ->whereIn('status', [Booking::STATUS_CONFIRMED, 'paid', 'confirmed']);
                })
                ->count(),
        ];
    }

    /**
     * Get recent transactions for today
     * Includes: confirmed bookings, used tickets, and tickets ready to validate (visit date = today)
     */
    public function getRecentTransactions(int $limit = 20)
    {
        return Booking::with(['payment', 'destination', 'tickets'])
            ->where(function ($q) {
                // Bookings confirmed today
                $q->whereDate('confirmed_at', today())
                    // OR bookings with tickets used today
                    ->orWhereHas('tickets', function ($ticketQuery) {
                    $ticketQuery->whereDate('used_at', today());
                })
                    // OR confirmed bookings with valid tickets for today (ready to validate)
                    ->orWhere(function ($subQ) {
                    $subQ->whereIn('status', [Booking::STATUS_CONFIRMED, 'paid'])
                        ->whereDate('visit_date', today())
                        ->whereHas('tickets', function ($ticketQuery) {
                            $ticketQuery->where('status', Ticket::STATUS_VALID);
                        });
                });
            })
            ->latest('updated_at')
            ->take($limit)
            ->get();
    }

    /**
     * Find ticket by code
     */
    public function findTicket(string $code): ?Ticket
    {
        $code = strtoupper(trim($code));

        $ticket = Ticket::with(['booking', 'booking.destination', 'booking.payment', 'booking.user'])
            ->where('ticket_code', $code)
            ->first();

        if (!$ticket && !str_starts_with($code, 'TKT-')) {
            $ticket = Ticket::with(['booking', 'booking.destination', 'booking.payment', 'booking.user'])
                ->where('ticket_code', 'TKT-' . $code)
                ->first();
        }

        return $ticket;
    }

    /**
     * Find booking by order number
     */
    public function findBookingByCode(string $code): ?Booking
    {
        return Booking::with(['tickets', 'destination', 'payment', 'user'])
            ->where('order_number', 'LIKE', "%{$code}%")
            ->first();
    }

    /**
     * Process cash payment for booking
     */
    public function processCashPayment(Booking $booking): array
    {
        // Check if booking is already paid or has existing payment
        if ($booking->isPaid()) {
            return [
                'success' => true,
                'message' => 'Pembayaran sudah lunas sebelumnya.',
                'booking' => $booking->fresh(['payment', 'destination', 'tickets']),
            ];
        }

        // Check if payment record already exists for this order
        $existingPayment = Payment::where('order_id', $booking->order_number)->first();
        if ($existingPayment) {
            // Update booking status if payment exists but booking not updated
            $booking->update([
                'status' => Booking::STATUS_CONFIRMED,
                'confirmed_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'Pembayaran sudah tercatat sebelumnya.',
                'booking' => $booking->fresh(['payment', 'destination', 'tickets']),
            ];
        }

        DB::beginTransaction();
        try {
            Payment::create([
                'booking_id' => $booking->id,
                'transaction_id' => 'CASH-' . now()->format('YmdHis') . '-' . $booking->id,
                'order_id' => $booking->order_number,
                'payment_type' => 'cash',
                'payment_channel' => 'counter',
                'gross_amount' => $booking->total_amount,
                'status' => Payment::STATUS_SUCCESS,
                'paid_at' => now(),
            ]);

            $booking->update([
                'status' => Booking::STATUS_CONFIRMED,
                'confirmed_at' => now(),
            ]);

            // Generate tickets if not exists
            if ($booking->tickets->isEmpty()) {
                app(\App\Services\TicketService::class)->generateTicketsForBooking($booking);
            }

            // Reload tickets relationship to get freshly generated tickets
            $booking->load('tickets');

            // Update all tickets to valid status
            foreach ($booking->tickets as $ticket) {
                $ticket->update(['status' => Ticket::STATUS_VALID]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Pembayaran tunai berhasil dikonfirmasi!',
                'booking' => $booking->fresh(['payment', 'destination', 'tickets']),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()];
        }
    }

    /**
     * Validate ticket entry
     */
    public function validateTicketEntry(Ticket $ticket): array
    {
        if ($ticket->status === 'used') {
            return ['success' => false, 'message' => 'Tiket sudah digunakan sebelumnya.'];
        }

        if ($ticket->status !== 'valid') {
            return ['success' => false, 'message' => 'Tiket tidak dalam status valid.'];
        }

        // Update ticket to used
        $ticket->update([
            'status' => 'used',
            'used_at' => now(),
            'validated_by' => Auth::guard('admin')->id(),
        ]);

        // Update booking status if all tickets are now used
        $booking = Booking::find($ticket->booking_id);
        if ($booking) {
            // Count remaining valid (unused) tickets - reload from DB to get fresh data
            $remainingValid = Ticket::where('booking_id', $booking->id)
                ->where('status', '!=', 'used')
                ->count();

            // If all tickets are used, update booking to 'used' status
            if ($remainingValid === 0 && in_array($booking->status, [Booking::STATUS_CONFIRMED, 'paid', 'confirmed'])) {
                $booking->status = Booking::STATUS_USED;
                $booking->used_at = now();
                $booking->save();
            }
        }

        return [
            'success' => true,
            'message' => 'Tiket berhasil divalidasi! Silakan masuk.',
            'ticket' => $ticket->fresh(),
        ];
    }

    /**
     * Check ticket status and return appropriate response
     */
    public function checkTicketStatus(Ticket $ticket): array
    {
        $booking = $ticket->booking;

        if ($booking && $booking->status === Booking::STATUS_PENDING) {
            return [
                'valid' => false,
                'status' => 'payment_required',
                'title' => 'Menunggu Pembayaran',
                'message' => 'Tiket ini belum dibayar. Silakan konfirmasi pembayaran tunai.',
            ];
        }

        if ($ticket->status === 'used') {
            return [
                'valid' => false,
                'status' => 'already_used',
                'title' => 'Tiket Sudah Digunakan',
                'message' => 'Tiket ini sudah divalidasi masuk.',
            ];
        }

        if ($ticket->status === 'expired' || ($ticket->valid_date && $ticket->valid_date->isPast() && !$ticket->valid_date->isToday())) {
            return [
                'valid' => false,
                'status' => 'expired',
                'title' => 'Tiket Kadaluarsa',
                'message' => 'Tiket ini sudah melewati tanggal berlaku.',
            ];
        }

        if ($ticket->valid_date && $ticket->valid_date->isFuture()) {
            return [
                'valid' => false,
                'status' => 'not_yet_valid',
                'title' => 'Tiket Belum Berlaku',
                'message' => "Tiket ini baru berlaku pada tanggal {$ticket->valid_date->format('d M Y')}.",
            ];
        }

        return [
            'valid' => true,
            'status' => 'ready_for_entry',
            'title' => 'Tiket Valid ✓',
            'message' => 'Tiket sudah dibayar dan siap untuk masuk.',
        ];
    }

    /**
     * Format ticket detail for response
     */
    public function formatTicketDetail(Ticket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'code' => $ticket->ticket_code,
            'status' => $ticket->status,
            'status_label' => $this->getTicketStatusLabel($ticket->status),
            'status_color' => $this->getTicketStatusColor($ticket->status),
            'valid_date' => $ticket->valid_date?->format('d M Y'),
            'valid_date_day' => $ticket->valid_date?->translatedFormat('l'),
            'used_at' => $ticket->used_at?->format('d M Y H:i'),
            'visitors' => $ticket->total_persons ?? 1,
        ];
    }

    /**
     * Format booking detail for response
     */
    public function formatBookingDetail(Booking $booking): array
    {
        $payment = $booking->payment;

        return [
            'id' => $booking->id,
            'order_number' => $booking->order_number,
            'customer_name' => $booking->leader_name,
            'customer_phone' => $booking->leader_phone,
            'customer_email' => $booking->leader_email,
            'destination' => $booking->destination?->name ?? '-',
            'visit_date' => $booking->visit_date?->format('d M Y'),
            'visit_day' => $booking->visit_date?->translatedFormat('l'),
            'total_visitors' => $booking->total_visitors,
            'adults' => $booking->total_adults,
            'children' => $booking->total_children,
            'seniors' => $booking->total_seniors,
            'total_amount' => (float) $booking->total_amount,
            'formatted_amount' => 'Rp ' . number_format($booking->total_amount ?? 0, 0, ',', '.'),
            'status' => $booking->status,
            'status_label' => $booking->status_label,
            'status_color' => $booking->status_color,
            'payment' => $payment ? [
                'type' => $payment->payment_type,
                'type_label' => $payment->payment_type_label,
                'channel' => $payment->payment_channel,
                'paid_at' => $payment->paid_at?->format('d M Y'),
                'paid_time' => $payment->paid_at?->format('H:i'),
                'paid_datetime' => $payment->paid_at?->format('d M Y H:i'),
                'amount' => $payment->formatted_amount,
            ] : null,
            'confirmed_at' => $booking->confirmed_at?->format('d M Y H:i'),
            'created_at' => $booking->created_at?->format('d M Y H:i'),
        ];
    }

    protected function getTicketStatusLabel(string $status): string
    {
        return match ($status) {
            'valid' => 'Valid',
            'used' => 'Sudah Digunakan',
            'expired' => 'Kadaluarsa',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($status),
        };
    }

    protected function getTicketStatusColor(string $status): string
    {
        return match ($status) {
            'valid' => 'green',
            'used' => 'blue',
            'expired' => 'red',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }
}
