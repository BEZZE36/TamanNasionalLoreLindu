<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleView;
use App\Models\ArticleComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class NewsDashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', '30');

        $stats = [
            'total' => Article::where('category', 'berita')->count(),
            'published' => Article::where('category', 'berita')->published()->count(),
            'draft' => Article::where('category', 'berita')->draft()->count(),
            'archived' => Article::where('category', 'berita')->archived()->count(),
            'scheduled' => Article::where('category', 'berita')->scheduled()->count(),
            'featured' => Article::where('category', 'berita')->featured()->count(),
            'pinned' => Article::where('category', 'berita')->pinned()->count(),
            'total_views' => Article::where('category', 'berita')->sum('views_count'),
            'total_likes' => Article::where('category', 'berita')->sum('likes_count'),
            'total_comments' => ArticleComment::whereHas('article', fn($q) => $q->where('category', 'berita'))->count(),
        ];

        $viewsChart = $this->getViewsChartData($period);

        $topNews = Article::where('category', 'berita')
            ->published()
            ->orderBy('views_count', 'desc')
            ->take(10)
            ->get(['id', 'title', 'slug', 'views_count', 'likes_count', 'published_at']);

        $recentNews = Article::where('category', 'berita')
            ->latest()
            ->take(5)
            ->get(['id', 'title', 'is_published', 'is_featured', 'created_at', 'updated_at']);

        $topReferrers = $this->getTopReferrers($period);
        $peakHours = $this->getPeakHours($period);
        $deviceBreakdown = $this->getDeviceBreakdown($period);
        $avgReadTime = $this->getAverageReadTime($period);
        $bounceRate = $this->getBounceRate($period);

        return Inertia::render('Admin/News/Dashboard', [
            'stats' => $stats,
            'viewsChart' => $viewsChart,
            'topNews' => $topNews,
            'recentNews' => $recentNews,
            'topReferrers' => $topReferrers,
            'peakHours' => $peakHours,
            'deviceBreakdown' => $deviceBreakdown,
            'avgReadTime' => $avgReadTime,
            'bounceRate' => $bounceRate,
            'period' => $period,
        ]);
    }

    private function getViewsChartData(int $days): array
    {
        $startDate = Carbon::now()->subDays($days);

        $views = ArticleView::whereHas('article', fn($q) => $q->where('category', 'berita'))
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $data = [];
        $labels = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($date)->format('d M');
            $data[] = $views->firstWhere('date', $date)?->count ?? 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function getTopReferrers(int $days): array
    {
        $startDate = Carbon::now()->subDays($days);

        return ArticleView::whereHas('article', fn($q) => $q->where('category', 'berita'))
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('referer')
            ->where('referer', '!=', '')
            ->select(
                DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(REPLACE(REPLACE(referer, 'https://', ''), 'http://', ''), '/', 1), '?', 1) as domain"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('domain')
            ->orderByDesc('count')
            ->take(10)
            ->get()
            ->toArray();
    }

    private function getPeakHours(int $days): array
    {
        $startDate = Carbon::now()->subDays($days);

        $hours = ArticleView::whereHas('article', fn($q) => $q->where('category', 'berita'))
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $data = array_fill(0, 24, 0);
        foreach ($hours as $hour) {
            $data[$hour->hour] = $hour->count;
        }

        return [
            'labels' => array_map(fn($h) => sprintf('%02d:00', $h), range(0, 23)),
            'data' => $data,
        ];
    }

    private function getDeviceBreakdown(int $days): array
    {
        $startDate = Carbon::now()->subDays($days);

        return ArticleView::whereHas('article', fn($q) => $q->where('category', 'berita'))
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('device_type')
            ->select('device_type', DB::raw('COUNT(*) as count'))
            ->groupBy('device_type')
            ->get()
            ->pluck('count', 'device_type')
            ->toArray();
    }

    private function getAverageReadTime(int $days): float
    {
        $startDate = Carbon::now()->subDays($days);

        $avg = ArticleView::whereHas('article', fn($q) => $q->where('category', 'berita'))
            ->where('created_at', '>=', $startDate)
            ->where('time_spent', '>', 0)
            ->avg('time_spent');

        return round($avg ?? 0, 1);
    }

    private function getBounceRate(int $days): float
    {
        $startDate = Carbon::now()->subDays($days);

        $total = ArticleView::whereHas('article', fn($q) => $q->where('category', 'berita'))
            ->where('created_at', '>=', $startDate)
            ->count();

        if ($total === 0)
            return 0;

        $bounced = ArticleView::whereHas('article', fn($q) => $q->where('category', 'berita'))
            ->where('created_at', '>=', $startDate)
            ->where('time_spent', '<', 10)
            ->count();

        return round(($bounced / $total) * 100, 1);
    }
}
