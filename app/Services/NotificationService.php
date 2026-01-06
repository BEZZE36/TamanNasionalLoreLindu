<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Notification;
use App\Models\Ticket;

class NotificationService
{
    /**
     * Notify user that booking was created successfully.
     */
    public function notifyBookingCreated(Booking $booking): Notification
    {
        return Notification::createForUser(
            $booking->user_id,
            Notification::TYPE_BOOKING_CREATED,
            'Pemesanan Berhasil Dibuat',
            "Pemesanan Anda untuk {$booking->destination->name} pada tanggal " . $booking->visit_date->format('d M Y') . " berhasil dibuat. Silakan lakukan pembayaran.",
            [
                'booking_id' => $booking->id,
                'order_number' => $booking->order_number,
                'destination' => $booking->destination->name,
                'amount' => $booking->total_amount,
            ],
            route('booking.payment', $booking->order_number)
        );
    }

    /**
     * Notify user that payment was successful.
     */
    public function notifyPaymentSuccess(Booking $booking): Notification
    {
        return Notification::createForUser(
            $booking->user_id,
            Notification::TYPE_PAYMENT_SUCCESS,
            'Pembayaran Berhasil!',
            "Pembayaran untuk {$booking->destination->name} senilai Rp " . number_format($booking->total_amount, 0, ',', '.') . " telah berhasil. Tiket Anda sudah siap!",
            [
                'booking_id' => $booking->id,
                'order_number' => $booking->order_number,
                'destination' => $booking->destination->name,
                'amount' => $booking->total_amount,
            ],
            route('booking.success', $booking->order_number)
        );
    }

    /**
     * Notify user about pending payment.
     */
    public function notifyPaymentPending(Booking $booking): Notification
    {
        return Notification::createForUser(
            $booking->user_id,
            Notification::TYPE_PAYMENT_PENDING,
            'Menunggu Pembayaran',
            "Pembayaran untuk pemesanan {$booking->order_number} sedang diproses. Mohon selesaikan pembayaran Anda.",
            [
                'booking_id' => $booking->id,
                'order_number' => $booking->order_number,
            ],
            route('booking.payment', $booking->order_number)
        );
    }

    /**
     * Notify user that payment failed.
     */
    public function notifyPaymentFailed(Booking $booking, ?string $reason = null): Notification
    {
        $message = "Pembayaran untuk pemesanan {$booking->order_number} gagal.";
        if ($reason) {
            $message .= " Alasan: {$reason}";
        }
        $message .= " Silakan coba lagi.";

        return Notification::createForUser(
            $booking->user_id,
            Notification::TYPE_PAYMENT_FAILED,
            'Pembayaran Gagal',
            $message,
            [
                'booking_id' => $booking->id,
                'order_number' => $booking->order_number,
                'reason' => $reason,
            ],
            route('booking.payment', $booking->order_number)
        );
    }

    /**
     * Notify user that payment/booking expired.
     */
    public function notifyPaymentExpired(Booking $booking): Notification
    {
        return Notification::createForUser(
            $booking->user_id,
            Notification::TYPE_PAYMENT_EXPIRED,
            'Pemesanan Kedaluwarsa',
            "Pemesanan {$booking->order_number} telah kedaluwarsa karena tidak ada pembayaran. Silakan buat pemesanan baru.",
            [
                'booking_id' => $booking->id,
                'order_number' => $booking->order_number,
            ],
            route('destinations.show', $booking->destination->slug)
        );
    }

    /**
     * Notify user about cash payment confirmation.
     */
    public function notifyCashPaymentConfirmed(Booking $booking): Notification
    {
        $expiryText = $booking->expired_at ? $booking->expired_at->format('d M Y H:i') : 'batas waktu yang ditentukan';
        
        return Notification::createForUser(
            $booking->user_id,
            Notification::TYPE_CASH_REMINDER,
            'Pembayaran Tunai Dikonfirmasi',
            "Pemesanan {$booking->order_number} dikonfirmasi untuk pembayaran tunai. Harap bayar di loket sebelum {$expiryText}.",
            [
                'booking_id' => $booking->id,
                'order_number' => $booking->order_number,
                'expired_at' => $booking->expired_at?->toISOString(),
            ],
            route('user.bookings.show', $booking->order_number)
        );
    }

    /**
     * Notify user when ticket is validated.
     */
    public function notifyTicketValidated(Ticket $ticket): Notification
    {
        $booking = $ticket->booking;
        
        return Notification::createForUser(
            $booking->user_id,
            Notification::TYPE_TICKET_VALIDATED,
            'Tiket Divalidasi',
            "Tiket Anda untuk {$booking->destination->name} telah berhasil divalidasi. Selamat menikmati kunjungan!",
            [
                'ticket_id' => $ticket->id,
                'ticket_code' => $ticket->ticket_code,
                'booking_id' => $booking->id,
            ],
            route('user.bookings.show', $booking->order_number)
        );
    }

    /**
     * Notify user when booking is cancelled.
     */
    public function notifyBookingCancelled(Booking $booking, ?string $reason = null): Notification
    {
        $message = "Pemesanan {$booking->order_number} untuk {$booking->destination->name} telah dibatalkan.";
        if ($reason) {
            $message .= " Alasan: {$reason}";
        }

        return Notification::createForUser(
            $booking->user_id,
            Notification::TYPE_BOOKING_CANCELLED,
            'Pemesanan Dibatalkan',
            $message,
            [
                'booking_id' => $booking->id,
                'order_number' => $booking->order_number,
                'reason' => $reason,
            ],
            route('destinations.show', $booking->destination->slug)
        );
    }

    /**
     * Create a general notification.
     */
    public function notify(int $userId, string $title, string $message, ?string $link = null): Notification
    {
        return Notification::createForUser(
            $userId,
            Notification::TYPE_GENERAL,
            $title,
            $message,
            null,
            $link
        );
    }
}
