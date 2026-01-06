<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id',
        'destination_id',
        'display_name',
        'is_anonymous',
        'subject',
        'message',
        'contact_whatsapp',
        'contact_email',
        'rating',
        'status',
        'is_published',
        'ip_address',
        'images',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'is_published' => 'boolean',
            'rating' => 'integer',
            'images' => 'array',
        ];
    }

    // Status constants
    public const STATUS_UNREAD = 'unread';
    public const STATUS_READ = 'read';
    public const STATUS_REPLIED = 'replied';
    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_UNREAD => 'Belum Dibaca',
        self::STATUS_READ => 'Sudah Dibaca',
        self::STATUS_REPLIED => 'Sudah Dibalas',
        self::STATUS_ARCHIVED => 'Diarsipkan',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(FeedbackReply::class);
    }

    public function latestReply(): HasOne
    {
        return $this->hasOne(FeedbackReply::class)->latest();
    }

    public function publicReplies(): HasMany
    {
        return $this->hasMany(FeedbackReply::class)->where('is_public', true);
    }

    // Accessors
    public function getDisplayNameTextAttribute(): string
    {
        if ($this->is_anonymous) {
            return 'Anonim';
        }
        return $this->display_name ?: ($this->user?->name ?? 'Pengunjung');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_UNREAD => 'red',
            self::STATUS_READ => 'yellow',
            self::STATUS_REPLIED => 'green',
            self::STATUS_ARCHIVED => 'gray',
            default => 'gray',
        };
    }

    // Status checks
    public function isUnread(): bool
    {
        return $this->status === self::STATUS_UNREAD;
    }

    public function isReplied(): bool
    {
        return $this->status === self::STATUS_REPLIED;
    }

    public function hasPublicReply(): bool
    {
        return $this->publicReplies()->exists();
    }

    // Actions
    public function markAsRead(): void
    {
        if ($this->status === self::STATUS_UNREAD) {
            $this->update(['status' => self::STATUS_READ]);
        }
    }

    public function archive(): void
    {
        $this->update(['status' => self::STATUS_ARCHIVED]);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('status', self::STATUS_UNREAD);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeNotArchived($query)
    {
        return $query->where('status', '!=', self::STATUS_ARCHIVED);
    }

    public function scopeWithRating($query)
    {
        return $query->whereNotNull('rating');
    }
}
