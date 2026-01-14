<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotificationService
{
    protected ?WebPush $webPush = null;

    /**
     * Initialize the WebPush instance.
     */
    protected function getWebPush(): ?WebPush
    {
        if ($this->webPush !== null) {
            return $this->webPush;
        }

        $publicKey = config('services.vapid.public_key');
        $privateKey = config('services.vapid.private_key');
        $subject = config('services.vapid.subject', 'mailto:admin@tnll.id');

        if (!$publicKey || !$privateKey) {
            Log::warning('VAPID keys not configured. Push notifications disabled.');
            return null;
        }

        // Debug: Log key lengths
        Log::info('VAPID keys loaded', [
            'public_key_length' => strlen($publicKey),
            'private_key_length' => strlen($privateKey),
            'subject' => $subject,
        ]);

        try {
            $this->webPush = new WebPush([
                'VAPID' => [
                    'subject' => $subject,
                    'publicKey' => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ]);

            // Auto flush after each send
            $this->webPush->setAutomaticPadding(true);

            return $this->webPush;
        } catch (\Exception $e) {
            Log::error('Failed to initialize WebPush: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Send a push notification to a specific user.
     */
    public function sendToUser(int $userId, string $title, string $body, ?string $url = null, ?string $icon = null): array
    {
        $subscriptions = PushSubscription::forUser($userId)->active()->get();
        return $this->sendToSubscriptions($subscriptions, $title, $body, $url, $icon);
    }

    /**
     * Send push notification to multiple users.
     */
    public function sendToUsers(array $userIds, string $title, string $body, ?string $url = null, ?string $icon = null): array
    {
        $subscriptions = PushSubscription::whereIn('user_id', $userIds)->active()->get();
        return $this->sendToSubscriptions($subscriptions, $title, $body, $url, $icon);
    }

    /**
     * Broadcast a push notification to all subscribers.
     */
    public function broadcast(string $title, string $body, ?string $url = null, ?string $icon = null): array
    {
        $subscriptions = PushSubscription::active()->get();
        return $this->sendToSubscriptions($subscriptions, $title, $body, $url, $icon);
    }

    /**
     * Send push notification to a collection of subscriptions.
     */
    protected function sendToSubscriptions($subscriptions, string $title, string $body, ?string $url = null, ?string $icon = null): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'total' => $subscriptions->count(),
        ];

        $webPush = $this->getWebPush();
        if (!$webPush) {
            return $results;
        }

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'url' => $url ?? '/',
            'icon' => $icon ?? '/assets/logo.png',
            'badge' => '/assets/logo.png',
            'timestamp' => time() * 1000,
        ]);

        foreach ($subscriptions as $subscription) {
            try {
                $webPushSubscription = Subscription::create([
                    'endpoint' => $subscription->endpoint,
                    'publicKey' => $subscription->p256dh_key,
                    'authToken' => $subscription->auth_key,
                    'contentEncoding' => $subscription->content_encoding ?? 'aes128gcm',
                ]);

                $webPush->queueNotification($webPushSubscription, $payload);
            } catch (\Exception $e) {
                Log::error('Failed to queue push notification', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                $results['failed']++;
            }
        }

        // Send all queued notifications
        try {
            foreach ($webPush->flush() as $report) {
                if ($report->isSuccess()) {
                    $results['success']++;
                } else {
                    $results['failed']++;

                    // If subscription is expired/invalid, mark it as inactive
                    $endpoint = $report->getEndpoint();
                    if ($report->isSubscriptionExpired()) {
                        PushSubscription::where('endpoint', $endpoint)->update(['is_active' => false]);
                        Log::info('Marked subscription as inactive (expired)', ['endpoint' => substr($endpoint, 0, 50)]);
                    } else {
                        Log::warning('Push notification failed', [
                            'endpoint' => substr($endpoint, 0, 50),
                            'reason' => $report->getReason(),
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to flush push notifications: ' . $e->getMessage(), [
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $results;
    }

    // ============================
    // NEW CONTENT NOTIFICATIONS
    // ============================

    /**
     * Notify all subscribers about a new destination.
     */
    public function notifyNewDestination(string $name, string $description, string $url): array
    {
        $cleanDescription = strip_tags(html_entity_decode($description));
        $body = mb_strlen($cleanDescription) > 100
            ? mb_substr($cleanDescription, 0, 100) . '...'
            : $cleanDescription;

        return $this->broadcast(
            '🗺️ Destinasi Baru: ' . $name,
            $body ?: 'Destinasi baru telah ditambahkan!',
            $url
        );
    }

    /**
     * Notify all subscribers about new flora.
     */
    public function notifyNewFlora(string $name, string $description, string $url): array
    {
        // Strip HTML tags and decode entities
        $cleanDescription = strip_tags(html_entity_decode($description));
        $body = mb_strlen($cleanDescription) > 100
            ? mb_substr($cleanDescription, 0, 100) . '...'
            : $cleanDescription;

        return $this->broadcast(
            '🌿 Flora Baru: ' . $name,
            $body ?: 'Flora baru telah ditambahkan!',
            $url
        );
    }

    /**
     * Notify all subscribers about new fauna.
     */
    public function notifyNewFauna(string $name, string $description, string $url): array
    {
        $cleanDescription = strip_tags(html_entity_decode($description));
        $body = mb_strlen($cleanDescription) > 100
            ? mb_substr($cleanDescription, 0, 100) . '...'
            : $cleanDescription;

        return $this->broadcast(
            '🦋 Fauna Baru: ' . $name,
            $body ?: 'Fauna baru telah ditambahkan!',
            $url
        );
    }

    /**
     * Notify all subscribers about a new article.
     */
    public function notifyNewArticle(string $title, string $excerpt, string $url): array
    {
        $cleanExcerpt = strip_tags(html_entity_decode($excerpt));
        $body = mb_strlen($cleanExcerpt) > 100
            ? mb_substr($cleanExcerpt, 0, 100) . '...'
            : $cleanExcerpt;

        return $this->broadcast(
            '📰 Artikel Baru: ' . $title,
            $body ?: 'Artikel baru telah dipublikasikan!',
            $url
        );
    }

    /**
     * Notify all subscribers about new news.
     */
    public function notifyNewNews(string $title, string $excerpt, string $url): array
    {
        $cleanExcerpt = strip_tags(html_entity_decode($excerpt));
        $body = mb_strlen($cleanExcerpt) > 100
            ? mb_substr($cleanExcerpt, 0, 100) . '...'
            : $cleanExcerpt;

        return $this->broadcast(
            '🗞️ Berita Baru: ' . $title,
            $body ?: 'Berita baru telah dipublikasikan!',
            $url
        );
    }

    /**
     * Notify all subscribers about new gallery.
     */
    public function notifyNewGallery(string $title, string $description, string $url): array
    {
        $cleanDescription = strip_tags(html_entity_decode($description));
        $body = mb_strlen($cleanDescription) > 100
            ? mb_substr($cleanDescription, 0, 100) . '...'
            : $cleanDescription;

        return $this->broadcast(
            '🖼️ Galeri Baru: ' . $title,
            $body ?: 'Galeri baru telah ditambahkan!',
            $url
        );
    }

    // ============================
    // COMMENT REPLY NOTIFICATIONS
    // ============================

    /**
     * Notify user about a comment reply.
     */
    public function notifyCommentReply(int $userId, string $contentType, string $contentTitle, string $url): array
    {
        return $this->sendToUser(
            $userId,
            '💬 Balasan Komentar',
            "Admin membalas komentar Anda di {$contentType}: {$contentTitle}",
            $url
        );
    }

    /**
     * Notify multiple users about a comment reply in a thread.
     */
    public function notifyCommentReplyToThread(array $userIds, string $contentType, string $contentTitle, string $url): array
    {
        if (empty($userIds)) {
            return ['success' => 0, 'failed' => 0, 'total' => 0];
        }

        return $this->sendToUsers(
            $userIds,
            '💬 Balasan di Diskusi',
            "Ada balasan baru di diskusi {$contentType}: {$contentTitle}",
            $url
        );
    }
}
