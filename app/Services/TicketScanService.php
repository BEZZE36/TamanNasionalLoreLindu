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
        ];
    }

    /**
     * Get recent transactions for today
     */
    public function getRecentTransactions(int $limit = 10)
    {
        return Booking::with(['payment', 'destination', 'tickets'])
            ->where(function ($q) {
                $q->whereDate('confirmed_at', today())
                    ->orWhereDate('used_at', today());
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
        if ($booking->isPaid()) {
            return ['success' => true, 'message' => 'Pembayaran sudah lunas sebelumnya.'];
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
                'status' => Booking::STATUS_PAID,
                'confirmed_at' => now(),
            ]);

            foreach ($booking->tickets as $ticket) {
                $ticket->update(['status' => Ticket::STATUS_VALID]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Pembayaran tunai berhasil dikonfirmasi!',
                'booking' => $booking->fresh(['payment', 'destination']),
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

        $ticket->update([
            'status' => 'used',
            'used_at' => now(),
            'validated_by' => Auth::guard('admin')->id(),
        ]);

        if ($ticket->booking) {
            $allUsed = $ticket->booking->tickets()->where('status', '!=', 'used')->count() === 0;
            if ($allUsed) {
                $ticket->booking->update([
                    'status' => Booking::STATUS_USED,
                    'used_at' => now(),
                ]);
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

        if ($booking && in_array($booking->status, [Booking::STATUS_PENDING, Booking::STATUS_AWAITING_CASH])) {
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
