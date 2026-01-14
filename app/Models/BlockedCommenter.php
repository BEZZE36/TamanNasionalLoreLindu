<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedCommenter extends Model
{
    protected $fillable = [
        'user_id',
        'admin_id',
        'reason',
        'blocked_until',
    ];

    protected $casts = [
        'blocked_until' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Check if block is still active
     */
    public function isActive(): bool
    {
        if ($this->blocked_until === null) {
            return true; // Permanent block
        }
        return $this->blocked_until->isFuture();
    }

    /**
     * Check if a user is blocked
     */
    public static function isUserBlocked(int $userId): bool
    {
        $block = self::where('user_id', $userId)->first();

        if (!$block) {
            return false;
        }

        if (!$block->isActive()) {
            $block->delete(); // Clean up expired block
            return false;
        }

        return true;
    }
}
