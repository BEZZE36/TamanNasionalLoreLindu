<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleView;
use Illuminate\Http\Request;

class ArticleViewController extends Controller
{
    /**
     * Track an article view (called via AJAX from public pages)
     */
    public function track(Request $request, Article $article)
    {
        $request->validate([
            'referer' => 'nullable|string|max:500',
        ]);

        // Detect device type from user agent
        $userAgent = strtolower($request->userAgent() ?? '');
        $deviceType = 'desktop';
        if (preg_match('/mobile|android|iphone|ipod|blackberry|windows phone/i', $userAgent)) {
            $deviceType = 'mobile';
        } elseif (preg_match('/tablet|ipad/i', $userAgent)) {
            $deviceType = 'tablet';
        }

        // Extract browser and platform
        $browser = $this->detectBrowser($userAgent);
        $platform = $this->detectPlatform($userAgent);

        // Create view record
        $view = ArticleView::create([
            'article_id' => $article->id,
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->referer ?? $request->header('referer'),
            'device_type' => $deviceType,
            'browser' => $browser,
            'platform' => $platform,
        ]);

        // Increment article view count
        $article->increment('views_count');

        return response()->json([
            'success' => true,
            'view_id' => $view->id,
        ]);
    }

    /**
     * Track time spent on article
     */
    public function trackTimeSpent(Request $request, ArticleView $view)
    {
        $request->validate([
            'time_spent' => 'required|integer|min:0|max:3600', // Max 1 hour
        ]);

        $view->update([
            'time_spent' => $request->time_spent,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Get article stats (for admin or author)
     */
    public function stats(Article $article)
    {
        // Check if user has permission
        if (!auth()->check() || (auth('admin')->guest() && auth()->id() !== $article->user_id)) {
            abort(403);
        }

        $stats = [
            'total_views' => $article->views_count,
            'unique_views' => $article->views()->distinct('session_id')->count('session_id'),
            'avg_time_spent' => round($article->views()->avg('time_spent') ?? 0),
            'device_breakdown' => $article->views()
                ->selectRaw('device_type, COUNT(*) as count')
                ->groupBy('device_type')
                ->pluck('count', 'device_type'),
        ];

        return response()->json($stats);
    }

    /**
     * Simple browser detection
     */
    private function detectBrowser(string $userAgent): string
    {
        if (str_contains($userAgent, 'edg'))
            return 'Edge';
        if (str_contains($userAgent, 'chrome'))
            return 'Chrome';
        if (str_contains($userAgent, 'firefox'))
            return 'Firefox';
        if (str_contains($userAgent, 'safari'))
            return 'Safari';
        if (str_contains($userAgent, 'opera') || str_contains($userAgent, 'opr'))
            return 'Opera';
        if (str_contains($userAgent, 'msie') || str_contains($userAgent, 'trident'))
            return 'IE';
        return 'Other';
    }

    /**
     * Simple platform detection
     */
    private function detectPlatform(string $userAgent): string
    {
        if (str_contains($userAgent, 'windows'))
            return 'Windows';
        if (str_contains($userAgent, 'mac'))
            return 'macOS';
        if (str_contains($userAgent, 'linux'))
            return 'Linux';
        if (str_contains($userAgent, 'android'))
            return 'Android';
        if (str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipad'))
            return 'iOS';
        return 'Other';
    }
}
