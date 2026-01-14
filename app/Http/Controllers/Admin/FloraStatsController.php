<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flora;
use App\Models\FloraImage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;

class FloraStatsController extends Controller
{
    public function index()
    {
        $topViewed = Flora::orderBy('view_count', 'desc')->take(10)->get(['id', 'name', 'view_count', 'created_at']);

        $totalFlora = Flora::count();
        $totalViews = Flora::sum('view_count') ?? 0;
        $totalImages = FloraImage::count();
        $featuredCount = Flora::where('is_featured', true)->count();
        $activeCount = Flora::where('is_active', true)->count();
        $inactiveCount = $totalFlora - $activeCount;

        $recentFlora = Flora::latest()->take(10)->withCount('images')->get();

        $months = [];
        $viewsData = [];
        $floraCreated = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->translatedFormat('M Y');

            $viewsData[] = Flora::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)->sum('view_count') ?? 0;

            $floraCreated[] = Flora::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)->count();
        }

        $byCategory = Flora::select('category', DB::raw('count(*) as count'))->groupBy('category')->get();
        $byConservation = Flora::select('conservation_status', DB::raw('count(*) as count'))->groupBy('conservation_status')->get();

        $topByMediaCount = Flora::withCount('images')->orderByDesc('images_count')->take(5)->get(['id', 'name', 'created_at']);

        $avgViewsPerFlora = $totalFlora > 0 ? round($totalViews / $totalFlora, 1) : 0;
        $avgMediaPerFlora = $totalFlora > 0 ? round($totalImages / $totalFlora, 1) : 0;

        $thisWeekFlora = Flora::where('created_at', '>=', Carbon::now()->startOfWeek())->count();
        $thisWeekViews = Flora::where('created_at', '>=', Carbon::now()->startOfWeek())->sum('view_count') ?? 0;

        $thisMonthFlora = Flora::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->count();
        $thisMonthViews = Flora::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->sum('view_count') ?? 0;

        return Inertia::render('Admin/Flora/Stats', compact(
            'topViewed',
            'totalFlora',
            'totalViews',
            'totalImages',
            'featuredCount',
            'activeCount',
            'inactiveCount',
            'recentFlora',
            'months',
            'viewsData',
            'floraCreated',
            'byCategory',
            'byConservation',
            'topByMediaCount',
            'avgViewsPerFlora',
            'avgMediaPerFlora',
            'thisWeekFlora',
            'thisWeekViews',
            'thisMonthFlora',
            'thisMonthViews'
        ));
    }
}
