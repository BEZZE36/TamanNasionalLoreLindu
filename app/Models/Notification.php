<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'icon',
        'color',
        'link',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    // Notification types - Booking
    public const TYPE_BOOKING_CREATED = 'booking_created';
    public const TYPE_PAYMENT_SUCCESS = 'payment_success';
    public const TYPE_PAYMENT_PENDING = 'payment_pending';
    public const TYPE_PAYMENT_FAILED = 'payment_failed';
    public const TYPE_PAYMENT_EXPIRED = 'payment_expired';
    public const TYPE_CASH_REMINDER = 'cash_reminder';
    public const TYPE_TICKET_VALIDATED = 'ticket_validated';
    public const TYPE_BOOKING_CANCELLED = 'booking_cancelled';

    // Notification types - New Content
    public const TYPE_NEW_DESTINATION = 'new_destination';
    public const TYPE_NEW_FLORA = 'new_flora';
    public const TYPE_NEW_FAUNA = 'new_fauna';
    public const TYPE_NEW_ARTICLE = 'new_article';
    public const TYPE_NEW_NEWS = 'new_news';
    public const TYPE_NEW_GALLERY = 'new_gallery';

    // Notification types - Comments
    public const TYPE_COMMENT_REPLY = 'comment_reply';

    // Notification types - General
    public const TYPE_GENERAL = 'general';

    // Color mappings for UI
    public const COLORS = [
        self::TYPE_BOOKING_CREATED => 'blue',
        self::TYPE_PAYMENT_SUCCESS => 'green',
        self::TYPE_PAYMENT_PENDING => 'yellow',
        self::TYPE_PAYMENT_FAILED => 'red',
        self::TYPE_PAYMENT_EXPIRED => 'gray',
        self::TYPE_CASH_REMINDER => 'orange',
        self::TYPE_TICKET_VALIDATED => 'emerald',
        self::TYPE_BOOKING_CANCELLED => 'red',
        self::TYPE_NEW_DESTINATION => 'teal',
        self::TYPE_NEW_FLORA => 'green',
        self::TYPE_NEW_FAUNA => 'amber',
        self::TYPE_NEW_ARTICLE => 'indigo',
        self::TYPE_NEW_NEWS => 'purple',
        self::TYPE_NEW_GALLERY => 'pink',
        self::TYPE_COMMENT_REPLY => 'cyan',
        self::TYPE_GENERAL => 'primary',
    ];

    // Icon mappings for UI
    public const ICONS = [
        self::TYPE_BOOKING_CREATED => '🎫',
        self::TYPE_PAYMENT_SUCCESS => '✅',
        self::TYPE_PAYMENT_PENDING => '⏳',
        self::TYPE_PAYMENT_FAILED => '❌',
        self::TYPE_PAYMENT_EXPIRED => '⏰',
        self::TYPE_CASH_REMINDER => '💵',
        self::TYPE_TICKET_VALIDATED => '🎉',
        self::TYPE_BOOKING_CANCELLED => '🚫',
        self::TYPE_NEW_DESTINATION => '🗺️',
        self::TYPE_NEW_FLORA => '🌿',
        self::TYPE_NEW_FAUNA => '🦋',
        self::TYPE_NEW_ARTICLE => '📰',
        self::TYPE_NEW_NEWS => '🗞️',
        self::TYPE_NEW_GALLERY => '🖼️',
        self::TYPE_COMMENT_REPLY => '💬',
        self::TYPE_GENERAL => '🔔',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    public function getIconEmojiAttribute(): string
    {
        return self::ICONS[$this->type] ?? '🔔';
    }

    public function getColorClassAttribute(): string
    {
        $color = self::COLORS[$this->type] ?? 'primary';
        return match ($color) {
            'green' => 'bg-green-100 text-green-600 border-green-200',
            'red' => 'bg-red-100 text-red-600 border-red-200',
            'yellow' => 'bg-yellow-100 text-yellow-600 border-yellow-200',
            'orange' => 'bg-orange-100 text-orange-600 border-orange-200',
            'blue' => 'bg-blue-100 text-blue-600 border-blue-200',
            'emerald' => 'bg-emerald-100 text-emerald-600 border-emerald-200',
            'gray' => 'bg-gray-100 text-gray-600 border-gray-200',
            'teal' => 'bg-teal-100 text-teal-600 border-teal-200',
            'amber' => 'bg-amber-100 text-amber-600 border-amber-200',
            'indigo' => 'bg-indigo-100 text-indigo-600 border-indigo-200',
            'purple' => 'bg-purple-100 text-purple-600 border-purple-200',
            'pink' => 'bg-pink-100 text-pink-600 border-pink-200',
            'cyan' => 'bg-cyan-100 text-cyan-600 border-cyan-200',
            default => 'bg-primary-100 text-primary-600 border-primary-200',
        };
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    // Actions
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    // Static factory methods
    public static function createForUser(int $userId, string $type, string $title, string $message, ?array $data = null, ?string $link = null): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'icon' => self::ICONS[$type] ?? '🔔',
            'color' => self::COLORS[$type] ?? 'primary',
            'link' => $link,
        ]);
    }

    // Mark all as read for a user
    public static function markAllAsReadForUser(int $userId): int
    {
        return self::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    // Get unread count for user
    public static function getUnreadCountForUser(int $userId): int
    {
        return self::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }
}
