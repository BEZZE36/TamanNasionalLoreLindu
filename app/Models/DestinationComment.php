<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DestinationComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'destination_id',
        'user_id',
        'admin_id',
        'parent_id',
        'content',
        'is_visible',
        'is_pinned',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'is_pinned' => 'boolean',
        ];
    }

    /**
     * Get the destination that owns the comment.
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    /**
     * Get the user that wrote the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin that wrote the comment.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the parent comment.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(DestinationComment::class, 'parent_id');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(DestinationComment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    /**
     * Get the author name (user or admin).
     */
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

    /**
     * Get the author avatar.
     */
    public function getAuthorAvatarAttribute(): ?string
    {
        if ($this->admin) {
            return $this->admin->avatar ?? null;
        }
        if ($this->user) {
            return $this->user->avatar ?? null;
        }
        return null;
    }

    /**
     * Check if comment is from admin.
     */
    public function isFromAdmin(): bool
    {
        return $this->admin_id !== null;
    }

    /**
     * Scope for visible comments.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope for pinned comments.
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope for root comments (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
