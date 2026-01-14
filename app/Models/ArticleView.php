<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Agent\Agent;

class ArticleView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'article_id',
        'user_id',
        'ip_address',
        'user_agent',
        'referer',
        'device_type',
        'time_spent',
        'created_at',
    ];

    protected $casts = [
        'time_spent' => 'integer',
        'created_at' => 'datetime',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a view from request
     */
    public static function recordView(Article $article, $request, ?User $user = null): self
    {
        $deviceType = 'desktop';

        // Detect device type from user agent
        $userAgent = $request->userAgent() ?? '';
        if (preg_match('/mobile|android|iphone|ipad/i', $userAgent)) {
            $deviceType = preg_match('/ipad|tablet/i', $userAgent) ? 'tablet' : 'mobile';
        }

        return self::create([
            'article_id' => $article->id,
            'user_id' => $user?->id,
            'ip_address' => $request->ip(),
            'user_agent' => substr($userAgent, 0, 500),
            'referer' => substr($request->header('referer') ?? '', 0, 500),
            'device_type' => $deviceType,
            'created_at' => now(),
        ]);
    }

    /**
     * Update time spent on article
     */
    public function updateTimeSpent(int $seconds): void
    {
        $this->update(['time_spent' => $seconds]);
    }
}
