<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Ticket;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketService
{
    /**
     * Generate ticket for a booking
     */
    public function generateTicket(Booking $booking): Ticket
    {
        // Check if ticket already exists
        if ($booking->ticket) {
            return $booking->ticket;
        }

        // Create QR data
        $qrData = $this->generateQrData($booking);

        // Generate QR code image
        $qrCodePath = $this->generateQrCode($booking->order_number, $qrData);

        // Create ticket
        $ticket = Ticket::create([
            'booking_id' => $booking->id,
            'qr_code_path' => $qrCodePath,
            'qr_data' => encrypt($qrData),
            'valid_date' => $booking->visit_date,
            'total_persons' => $booking->total_visitors,
            'status' => Ticket::STATUS_VALID,
        ]);

        return $ticket;
    }

    /**
     * Alias for generateTicket to match Controller call
     */
    public function generateTicketsForBooking(Booking $booking)
    {
        return $this->generateTicket($booking);
    }

    /**
     * Generate QR data JSON - simplified with only essential info
     */
    protected function generateQrData(Booking $booking): string
    {
        $data = [
            'code' => '', // Will be filled after ticket creation
            'name' => $booking->leader_name,
            'order' => $booking->order_number,
            'visit' => $booking->visit_date->format('Y-m-d'),
            'booked' => $booking->created_at->format('Y-m-d'),
        ];

        return json_encode($data);
    }

    /**
     * Generate QR Code image and save to storage
     */
    protected function generateQrCode(string $orderNumber, string $data): string
    {
        $directory = 'tickets/' . now()->format('Y/m');
        $filename = $orderNumber . '.svg';
        $path = $directory . '/' . $filename;

        // Ensure directory exists
        Storage::disk('public')->makeDirectory($directory);

        // Generate QR code with logo
        $qrCode = QrCode::format('svg')
            ->size(400)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($data);

        // Save to storage
        Storage::disk('public')->put($path, $qrCode);

        return $path;
    }

    /**
     * Validate ticket by code
     */
    public function validateTicketByCode(string $code): array
    {
        // 1. Try finding by Ticket Code
        $ticket = Ticket::with(['booking.destination', 'booking.user'])->where('ticket_code', $code)->first();

        // 2. If not found, try finding by Booking Order Number
        if (!$ticket) {
            $booking = Booking::with(['destination', 'user', 'ticket'])->where('order_number', $code)->first();

            if ($booking) {
                if ($booking->ticket) {
                    $ticket = $booking->ticket;
                } else {
                    // Booking exists but no ticket generated (e.g. Pending, Cancelled, Expired)
                    $reason = match ($booking->status) {
                        Booking::STATUS_PENDING => 'pending_payment',
                        Booking::STATUS_AWAITING_CASH => 'awaiting_cash',
                        Booking::STATUS_CANCELLED => 'cancelled',
                        Booking::STATUS_EXPIRED => 'expired',
                        default => 'no_ticket'
                    };

                    $statusLabel = match ($booking->status) {
                        Booking::STATUS_PENDING => 'Menunggu Pembayaran',
                        Booking::STATUS_CANCELLED => 'Dibatalkan',
                        Booking::STATUS_EXPIRED => 'Kedaluwarsa',
                        Booking::STATUS_AWAITING_CASH => 'Menunggu Pembayaran Tunai',
                        default => $booking->status
                    };

                    return [
                        'valid' => false,
                        'reason' => $reason,
                        'message' => "Booking ditemukan (Status: {$statusLabel})",
                        'ticket' => null,
                        'booking' => $booking,
                    ];
                }
            }
        }

        if (!$ticket) {
            return [
                'valid' => false,
                'reason' => 'not_found',
                'message' => 'Tiket atau Order tidak ditemukan',
                'ticket' => null,
            ];
        }

        return $this->validateTicket($ticket);
    }

    /**
     * Validate ticket from QR data
     */
    public function validateFromQrData(string $qrData): array
    {
        try {
            $data = json_decode($qrData, true);

            if (!$data || !isset($data['order'])) {
                return [
                    'valid' => false,
                    'reason' => 'invalid_format',
                    'message' => 'Format QR Code tidak valid',
                    'ticket' => null,
                ];
            }

            // Find booking and ticket by order number
            $booking = Booking::with(['destination', 'user', 'ticket'])->where('order_number', $data['order'])->first();
            if (!$booking || !$booking->ticket) {
                return [
                    'valid' => false,
                    'reason' => 'not_found',
                    'message' => 'Booking tidak ditemukan',
                    'ticket' => null,
                ];
            }

            // Optionally verify ticket code if present
            if (!empty($data['code']) && $data['code'] !== $booking->ticket->ticket_code) {
                return [
                    'valid' => false,
                    'reason' => 'mismatch',
                    'message' => 'Kode tiket tidak cocok',
                    'ticket' => null,
                ];
            }

            return $this->validateTicket($booking->ticket);
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'reason' => 'error',
                'message' => 'Error validasi: ' . $e->getMessage(),
                'ticket' => null,
            ];
        }
    }

    /**
     * Validate ticket object
     */
    public function validateTicket(Ticket $ticket): array
    {
        // Check if already used
        if ($ticket->isUsed()) {
            return [
                'valid' => false,
                'reason' => 'used',
                'message' => 'Tiket sudah digunakan pada ' . $ticket->used_at->format('d M Y H:i'),
                'ticket' => $ticket,
            ];
        }

        // Check if expired
        if ($ticket->valid_date->isPast() && !$ticket->valid_date->isToday()) {
            return [
                'valid' => false,
                'reason' => 'expired',
                'message' => 'Tiket sudah kedaluwarsa (berlaku: ' . $ticket->valid_date->format('d M Y') . ')',
                'ticket' => $ticket,
            ];
        }

        // Check if for today
        if (!$ticket->valid_date->isToday()) {
            return [
                'valid' => false,
                'reason' => 'future_date',
                'message' => 'Tiket berlaku untuk tanggal ' . $ticket->valid_date->format('d M Y') . ', bukan hari ini',
                'ticket' => $ticket,
            ];
        }

        // Check if cancelled
        if ($ticket->status === Ticket::STATUS_CANCELLED) {
            return [
                'valid' => false,
                'reason' => 'cancelled',
                'message' => 'Tiket telah dibatalkan',
                'ticket' => $ticket,
            ];
        }

        // Valid!
        return [
            'valid' => true,
            'reason' => 'valid',
            'message' => 'Tiket valid untuk ' . $ticket->total_persons . ' orang',
            'ticket' => $ticket,
        ];
    }

    /**
     * Update QR data with ticket code (called after ticket creation)
     */
    public function updateQrWithTicketCode(Ticket $ticket): void
    {
        $qrData = json_decode(decrypt($ticket->qr_data), true);
        $qrData['code'] = $ticket->ticket_code;
        $newQrData = json_encode($qrData);

        // Regenerate QR code with updated data
        $qrCodePath = $this->generateQrCode($ticket->booking->order_number, $newQrData);

        $ticket->update([
            'qr_data' => encrypt($newQrData),
            'qr_code_path' => $qrCodePath,
        ]);
    }
}
