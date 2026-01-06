<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FloraComment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'admin_id', 'flora_id', 'content', 'is_approved', 'is_pinned', 'parent_id'];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'is_pinned' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function flora(): BelongsTo
    {
        return $this->belongsTo(Flora::class);
    }

    /**
     * Get the parent comment (for replies)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(FloraComment::class, 'parent_id');
    }

    /**
     * Get replies to this comment
     */
    public function replies(): HasMany
    {
        return $this->hasMany(FloraComment::class, 'parent_id')->with('user', 'admin', 'replies');
    }

    /**
     * Get author name (user or admin)
     */
    public function getAuthorNameAttribute(): string
    {
        if ($this->admin_id && $this->admin) {
            return $this->admin->name . ' (Admin)';
        }
        return $this->user->name ?? 'Unknown';
    }

    /**
     * Get author avatar
     */
    public function getAuthorAvatarAttribute(): string
    {
        if ($this->admin_id && $this->admin) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->admin->name) . '&background=059669&color=fff';
        }
        return $this->user->avatar_url ?? 'https://ui-avatars.com/api/?name=U&background=059669&color=fff';
    }

    /**
     * Scope for approved comments
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope for top-level comments (no parent)
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }
}
