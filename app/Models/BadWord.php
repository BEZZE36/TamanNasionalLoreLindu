<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BadWord extends Model
{
    protected $fillable = [
        'word',
        'replacement',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Filter text using all active bad words
     */
    public static function filterText(string $text): string
    {
        $badWords = self::where('is_active', true)->get();

        foreach ($badWords as $badWord) {
            $pattern = '/\b' . preg_quote($badWord->word, '/') . '\b/iu';
            $text = preg_replace($pattern, $badWord->replacement, $text);
        }

        return $text;
    }

    /**
     * Check if text contains bad words
     */
    public static function containsBadWords(string $text): bool
    {
        $badWords = self::where('is_active', true)->pluck('word')->toArray();

        foreach ($badWords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/iu', $text)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get list of detected bad words in text
     */
    public static function detectBadWords(string $text): array
    {
        $detected = [];
        $badWords = self::where('is_active', true)->pluck('word')->toArray();

        foreach ($badWords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/iu', $text)) {
                $detected[] = $word;
            }
        }

        return $detected;
    }
}
