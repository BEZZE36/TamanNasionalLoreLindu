<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\GalleryMedia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;

class GalleryStatsController extends Controller
{
    public function index()
    {
        // Top viewed galleries
        $topViewed = Gallery::orderBy('view_count', 'desc')
            ->take(10)
            ->get(['id', 'title', 'view_count', 'created_at']);

        // Total stats
        $totalGalleries = Gallery::count();
        $totalViews = Gallery::sum('view_count') ?? 0;
        $totalMedia = GalleryMedia::count();
        $totalImages = GalleryMedia::where('type', 'image')->count();
        $totalVideos = GalleryMedia::where('type', 'video')->count();
        $featuredCount = Gallery::where('is_featured', true)->count();
        $activeCount = Gallery::where('is_active', true)->count();
        $inactiveCount = $totalGalleries - $activeCount;

        // Recent galleries
        $recentGalleries = Gallery::latest()
            ->take(10)
            ->withCount('media')
            ->get();

        // Views by month (last 6 months in chart format)
        $months = [];
        $viewsData = [];
        $galleriesCreated = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthLabel = $date->translatedFormat('M Y');
            $months[] = $monthLabel;

            $monthViews = Gallery::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('view_count') ?? 0;
            $viewsData[] = $monthViews;

            $createdCount = Gallery::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $galleriesCreated[] = $createdCount;
        }

        // Galleries by category
        $byCategory = Gallery::select('gallery_category_id', DB::raw('count(*) as count'))
            ->with('category:id,name')
            ->groupBy('gallery_category_id')
            ->get();

        // Top galleries by media count
        $topByMediaCount = Gallery::withCount('media')
            ->orderByDesc('media_count')
            ->take(5)
            ->get(['id', 'title', 'created_at']);

        // Average stats
        $avgViewsPerGallery = $totalGalleries > 0 ? round($totalViews / $totalGalleries, 1) : 0;
        $avgMediaPerGallery = $totalGalleries > 0 ? round($totalMedia / $totalGalleries, 1) : 0;

        // This week stats
        $thisWeekGalleries = Gallery::where('created_at', '>=', Carbon::now()->startOfWeek())->count();
        $thisWeekViews = Gallery::where('created_at', '>=', Carbon::now()->startOfWeek())->sum('view_count') ?? 0;

        // This month stats
        $thisMonthGalleries = Gallery::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        $thisMonthViews = Gallery::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('view_count') ?? 0;

        return Inertia::render('Admin/Gallery/Stats', compact(
            'topViewed',
            'totalGalleries',
            'totalViews',
            'totalMedia',
            'totalImages',
            'totalVideos',
            'featuredCount',
            'activeCount',
            'inactiveCount',
            'recentGalleries',
            'months',
            'viewsData',
            'galleriesCreated',
            'byCategory',
            'topByMediaCount',
            'avgViewsPerGallery',
            'avgMediaPerGallery',
            'thisWeekGalleries',
            'thisWeekViews',
            'thisMonthGalleries',
            'thisMonthViews'
        ));
    }

    /**
     * Export gallery statistics
     */
    public function export(string $format = 'csv')
    {
        $galleries = Gallery::with(['destination', 'category'])
            ->withCount(['media', 'likes', 'comments'])
            ->orderBy('view_count', 'desc')
            ->get();

        if ($format === 'csv') {
            $filename = 'gallery-statistics-' . now()->format('Y-m-d') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($galleries) {
                $file = fopen('php://output', 'w');

                // UTF-8 BOM for Excel compatibility
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Header row
                fputcsv($file, [
                    'ID',
                    'Judul',
                    'Slug',
                    'Destinasi',
                    'Kategori',
                    'Lokasi',
                    'Jumlah Media',
                    'Jumlah Views',
                    'Jumlah Likes',
                    'Jumlah Komentar',
                    'Status',
                    'Unggulan',
                    'Tanggal Dibuat',
                ]);

                foreach ($galleries as $gallery) {
                    fputcsv($file, [
                        $gallery->id,
                        $gallery->title,
                        $gallery->slug,
                        $gallery->destination?->name ?? '-',
                        $gallery->category?->name ?? '-',
                        $gallery->location ?? '-',
                        $gallery->media_count,
                        $gallery->view_count ?? 0,
                        $gallery->likes_count ?? 0,
                        $gallery->comments_count ?? 0,
                        $gallery->is_active ? 'Aktif' : 'Draft',
                        $gallery->is_featured ? 'Ya' : 'Tidak',
                        $gallery->created_at->format('d/m/Y H:i'),
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // For PDF or other formats, you can add more handlers here
        return back()->with('error', 'Format tidak didukung');
    }
}

