<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fauna;
use App\Models\FaunaImage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;

class FaunaStatsController extends Controller
{
    public function index()
    {
        $topViewed = Fauna::orderBy('view_count', 'desc')->take(10)->get(['id', 'name', 'view_count', 'created_at']);

        $totalFauna = Fauna::count();
        $totalViews = Fauna::sum('view_count') ?? 0;
        $totalImages = FaunaImage::count();
        $featuredCount = Fauna::where('is_featured', true)->count();
        $activeCount = Fauna::where('is_active', true)->count();
        $inactiveCount = $totalFauna - $activeCount;

        $recentFauna = Fauna::latest()->take(10)->withCount('images')->get();

        $months = [];
        $viewsData = [];
        $faunaCreated = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->translatedFormat('M Y');

            $viewsData[] = Fauna::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)->sum('view_count') ?? 0;

            $faunaCreated[] = Fauna::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)->count();
        }

        $byCategory = Fauna::select('category', DB::raw('count(*) as count'))->groupBy('category')->get();
        $byConservation = Fauna::select('conservation_status', DB::raw('count(*) as count'))->groupBy('conservation_status')->get();

        $topByMediaCount = Fauna::withCount('images')->orderByDesc('images_count')->take(5)->get(['id', 'name', 'created_at']);

        $avgViewsPerFauna = $totalFauna > 0 ? round($totalViews / $totalFauna, 1) : 0;
        $avgMediaPerFauna = $totalFauna > 0 ? round($totalImages / $totalFauna, 1) : 0;

        $thisWeekFauna = Fauna::where('created_at', '>=', Carbon::now()->startOfWeek())->count();
        $thisWeekViews = Fauna::where('created_at', '>=', Carbon::now()->startOfWeek())->sum('view_count') ?? 0;

        $thisMonthFauna = Fauna::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->count();
        $thisMonthViews = Fauna::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->sum('view_count') ?? 0;

        return Inertia::render('Admin/Fauna/Stats', compact(
            'topViewed',
            'totalFauna',
            'totalViews',
            'totalImages',
            'featuredCount',
            'activeCount',
            'inactiveCount',
            'recentFauna',
            'months',
            'viewsData',
            'faunaCreated',
            'byCategory',
            'byConservation',
            'topByMediaCount',
            'avgViewsPerFauna',
            'avgMediaPerFauna',
            'thisWeekFauna',
            'thisWeekViews',
            'thisMonthFauna',
            'thisMonthViews'
        ));
    }
}
