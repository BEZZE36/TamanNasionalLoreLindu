<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Notification;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Str;

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

    // ============================
    // NEW CONTENT NOTIFICATIONS
    // ============================

    /**
     * Notify all users about new content (broadcast).
     */
    public function notifyNewContent(string $type, string $title, string $description, string $link): int
    {
        $message = Str::limit(strip_tags($description), 150);
        $userIds = User::pluck('id')->toArray();

        $count = 0;
        foreach ($userIds as $userId) {
            Notification::createForUser(
                $userId,
                $type,
                $title,
                $message ?: 'Konten baru telah ditambahkan!',
                null,
                $link
            );
            $count++;
        }

        return $count;
    }

    /**
     * Notify about new destination.
     */
    public function notifyNewDestination(string $name, string $description, string $link): int
    {
        return $this->notifyNewContent(
            Notification::TYPE_NEW_DESTINATION,
            "🗺️ Destinasi Baru: {$name}",
            $description,
            $link
        );
    }

    /**
     * Notify about new flora.
     */
    public function notifyNewFlora(string $name, string $description, string $link): int
    {
        return $this->notifyNewContent(
            Notification::TYPE_NEW_FLORA,
            "🌿 Flora Baru: {$name}",
            $description,
            $link
        );
    }

    /**
     * Notify about new fauna.
     */
    public function notifyNewFauna(string $name, string $description, string $link): int
    {
        return $this->notifyNewContent(
            Notification::TYPE_NEW_FAUNA,
            "🦋 Fauna Baru: {$name}",
            $description,
            $link
        );
    }

    /**
     * Notify about new article.
     */
    public function notifyNewArticle(string $title, string $excerpt, string $link): int
    {
        return $this->notifyNewContent(
            Notification::TYPE_NEW_ARTICLE,
            "📰 Artikel Baru: {$title}",
            $excerpt,
            $link
        );
    }

    /**
     * Notify about new news.
     */
    public function notifyNewNews(string $title, string $excerpt, string $link): int
    {
        return $this->notifyNewContent(
            Notification::TYPE_NEW_NEWS,
            "🗞️ Berita Baru: {$title}",
            $excerpt,
            $link
        );
    }

    /**
     * Notify about new gallery.
     */
    public function notifyNewGallery(string $title, string $description, string $link): int
    {
        return $this->notifyNewContent(
            Notification::TYPE_NEW_GALLERY,
            "🖼️ Galeri Baru: {$title}",
            $description,
            $link
        );
    }

    // ============================
    // COMMENT REPLY NOTIFICATIONS
    // ============================

    /**
     * Notify about comment reply.
     * Sends to original commenter + all thread participants.
     */
    public function notifyCommentReply(
        ?int $originalCommentUserId,
        array $participantUserIds,
        string $contentType,
        string $contentTitle,
        string $link
    ): int {
        // Combine and deduplicate user IDs
        $allUserIds = array_unique(array_filter(
            array_merge([$originalCommentUserId], $participantUserIds)
        ));

        $count = 0;
        foreach ($allUserIds as $userId) {
            // Different message for original commenter vs participants
            $isOriginal = $userId === $originalCommentUserId;
            $title = $isOriginal
                ? '💬 Admin Membalas Komentar Anda'
                : '💬 Balasan Baru di Diskusi';
            $message = $isOriginal
                ? "Admin telah membalas komentar Anda di {$contentType}: {$contentTitle}"
                : "Ada balasan baru di diskusi {$contentType}: {$contentTitle}";

            Notification::createForUser(
                $userId,
                Notification::TYPE_COMMENT_REPLY,
                $title,
                $message,
                [
                    'content_type' => $contentType,
                    'content_title' => $contentTitle,
                    'is_original_commenter' => $isOriginal,
                ],
                $link
            );
            $count++;
        }

        return $count;
    }
}
