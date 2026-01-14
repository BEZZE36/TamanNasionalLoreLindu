<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArticleComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'user_id',
        'admin_id',
        'parent_id',
        'content',
        'is_visible',
        'is_pinned',
        'likes_count',
        'status',
        'is_reported',
        'report_reason',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_pinned' => 'boolean',
        'is_reported' => 'boolean',
        'likes_count' => 'integer',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    // ========== RELATIONSHIPS ==========

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ArticleComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ArticleComment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'article_comment_likes', 'comment_id', 'user_id');
    }

    // ========== ACCESSORS ==========

    public function getAuthorNameAttribute(): string
    {
        if ($this->admin) {
            return $this->admin->name . ' (Admin)';
        }
        if ($this->user) {
            return $this->user->name;
        }
        return 'Anonim';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            default => 'Unknown',
        };
    }

    // ========== SCOPES ==========

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeReported($query)
    {
        return $query->where('is_reported', true);
    }

    // ========== HELPERS ==========

    public function approve(): void
    {
        $this->update(['status' => self::STATUS_APPROVED]);
    }

    public function reject(): void
    {
        $this->update(['status' => self::STATUS_REJECTED]);
    }

    public function report(string $reason = null): void
    {
        $this->update([
            'is_reported' => true,
            'report_reason' => $reason,
        ]);
    }

    public function clearReport(): void
    {
        $this->update([
            'is_reported' => false,
            'report_reason' => null,
        ]);
    }

    public function isLikedBy(?User $user): bool
    {
        if (!$user)
            return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function toggleLike(User $user): bool
    {
        if ($this->isLikedBy($user)) {
            $this->likes()->detach($user->id);
            $this->decrement('likes_count');
            return false;
        } else {
            $this->likes()->attach($user->id);
            $this->increment('likes_count');
            return true;
        }
    }

    /**
     * Filter content using bad words filter
     */
    public static function filterContent(string $content): string
    {
        return BadWord::filterText($content);
    }

    /**
     * Check if user can comment (not blocked)
     */
    public static function canUserComment(int $userId): bool
    {
        return !BlockedCommenter::isUserBlocked($userId);
    }
}
