<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'endpoint',
        'endpoint_hash',
        'p256dh_key',
        'auth_key',
        'content_encoding',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get subscriptions for a specific user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Subscribe a user with push subscription details.
     */
    public static function subscribe(int $userId, array $subscription): self
    {
        $endpointHash = hash('sha256', $subscription['endpoint']);

        return self::updateOrCreate(
            [
                'user_id' => $userId,
                'endpoint_hash' => $endpointHash,
            ],
            [
                'endpoint' => $subscription['endpoint'],
                'p256dh_key' => $subscription['keys']['p256dh'] ?? null,
                'auth_key' => $subscription['keys']['auth'] ?? null,
                'content_encoding' => $subscription['contentEncoding'] ?? 'aes128gcm',
                'is_active' => true,
            ]
        );
    }

    /**
     * Unsubscribe by endpoint.
     */
    public static function unsubscribeByEndpoint(int $userId, string $endpoint): bool
    {
        return self::where('user_id', $userId)
            ->where('endpoint', $endpoint)
            ->delete() > 0;
    }

    /**
     * Get all active subscriptions for broadcasting.
     */
    public static function allActiveSubscriptions()
    {
        return self::active()->get();
    }
}
