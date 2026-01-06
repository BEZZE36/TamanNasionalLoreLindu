<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FaunaComment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'admin_id', 'fauna_id', 'content', 'is_approved', 'is_pinned', 'parent_id'];

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

    public function fauna(): BelongsTo
    {
        return $this->belongsTo(Fauna::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(FaunaComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(FaunaComment::class, 'parent_id')->with('user', 'admin', 'replies');
    }

    public function getAuthorNameAttribute(): string
    {
        if ($this->admin_id && $this->admin) {
            return $this->admin->name . ' (Admin)';
        }
        return $this->user->name ?? 'Unknown';
    }

    public function getAuthorAvatarAttribute(): string
    {
        if ($this->admin_id && $this->admin) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->admin->name) . '&background=f59e0b&color=fff';
        }
        return $this->user->avatar_url ?? 'https://ui-avatars.com/api/?name=U&background=f59e0b&color=fff';
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }
}
