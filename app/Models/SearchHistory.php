<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'query',
        'result_type',
        'result_id',
        'result_title',
        'result_url',
    ];

    /**
     * Get the user that owns the search history.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get recent searches for a user
     */
    public static function getRecentForUser(int $userId, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('user_id', $userId)
            ->whereNotNull('result_url')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Add a search to history (avoid duplicates by updating timestamp)
     */
    public static function addToHistory(int $userId, array $data): self
    {
        // Check if same result exists, update instead of creating new
        $existing = static::where('user_id', $userId)
            ->where('result_url', $data['result_url'])
            ->first();

        if ($existing) {
            $existing->touch();
            return $existing;
        }

        // Create new entry
        return static::create(array_merge(['user_id' => $userId], $data));
    }

    /**
     * Clear all history for a user
     */
    public static function clearForUser(int $userId): void
    {
        static::where('user_id', $userId)->delete();
    }
}
