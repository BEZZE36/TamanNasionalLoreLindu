<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Booking;
use App\Models\DestinationImage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;

class DestinationStatsController extends Controller
{
    // Valid booking statuses for revenue/stats counting
    protected $validStatuses = ['paid', 'confirmed', 'used'];

    public function index()
    {
        // Top destinations by bookings
        $topByBookings = Destination::withCount('bookings')
            ->orderByDesc('bookings_count')
            ->take(10)
            ->get(['id', 'name', 'slug', 'rating', 'created_at']);

        // Top destinations by revenue
        $validStatuses = $this->validStatuses;
        $topByRevenue = Destination::select('destinations.*')
            ->selectRaw('COALESCE(SUM(bookings.total_amount), 0) as total_revenue')
            ->leftJoin('bookings', function ($join) use ($validStatuses) {
                $join->on('destinations.id', '=', 'bookings.destination_id')
                    ->whereIn('bookings.status', $validStatuses);
            })
            ->groupBy('destinations.id')
            ->orderByDesc('total_revenue')
            ->take(10)
            ->get();

        // Total stats
        $totalDestinations = Destination::count();
        $activeCount = Destination::where('is_active', true)->count();
        $featuredCount = Destination::where('is_featured', true)->count();
        $inactiveCount = $totalDestinations - $activeCount;

        // Total bookings and revenue
        $totalBookings = Booking::whereIn('status', $this->validStatuses)->count();
        $totalRevenue = Booking::whereIn('status', $this->validStatuses)->sum('total_amount') ?? 0;
        $totalVisitors = Booking::whereIn('status', $this->validStatuses)->sum('total_visitors') ?? 0;

        $totalImages = DestinationImage::count();
        $totalViews = Destination::sum('views_count') ?? 0;
        $avgRating = Destination::where('rating', '>', 0)->avg('rating') ?? 0;

        $recentDestinations = Destination::latest()->withCount(['images', 'bookings'])->take(10)->get();

        // Chart data
        $months = [];
        $bookingsData = [];
        $revenueData = [];
        $destinationsCreated = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->translatedFormat('M Y');

            $bookingsData[] = Booking::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->whereIn('status', $this->validStatuses)->count();

            $revenueData[] = Booking::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->whereIn('status', $this->validStatuses)->sum('total_amount') ?? 0;

            $destinationsCreated[] = Destination::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)->count();
        }

        $byCategory = Destination::select('category_id', DB::raw('count(*) as count'))
            ->with('category:id,name')->whereNotNull('category_id')->groupBy('category_id')->get();

        $byCity = Destination::select('city', DB::raw('count(*) as count'))
            ->groupBy('city')->orderByDesc('count')->take(10)->get();

        $thisWeekBookings = Booking::where('created_at', '>=', Carbon::now()->startOfWeek())
            ->whereIn('status', $this->validStatuses)->count();
        $thisWeekRevenue = Booking::where('created_at', '>=', Carbon::now()->startOfWeek())
            ->whereIn('status', $this->validStatuses)->sum('total_amount') ?? 0;

        $thisMonthBookings = Booking::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereIn('status', $this->validStatuses)->count();
        $thisMonthRevenue = Booking::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereIn('status', $this->validStatuses)->sum('total_amount') ?? 0;

        $avgBookingsPerDestination = $totalDestinations > 0 ? round($totalBookings / $totalDestinations, 1) : 0;
        $avgRevenuePerDestination = $totalDestinations > 0 ? round($totalRevenue / $totalDestinations, 0) : 0;

        return Inertia::render('Admin/Destinations/Stats', compact(
            'topByBookings',
            'topByRevenue',
            'totalDestinations',
            'activeCount',
            'featuredCount',
            'inactiveCount',
            'totalBookings',
            'totalRevenue',
            'totalVisitors',
            'totalViews',
            'totalImages',
            'avgRating',
            'recentDestinations',
            'months',
            'bookingsData',
            'revenueData',
            'destinationsCreated',
            'byCategory',
            'byCity',
            'thisWeekBookings',
            'thisWeekRevenue',
            'thisMonthBookings',
            'thisMonthRevenue',
            'avgBookingsPerDestination',
            'avgRevenuePerDestination'
        ));
    }

    /**
     * Export destination statistics
     */
    public function export(string $format = 'csv')
    {
        $destinations = Destination::with(['category', 'images'])
            ->withCount(['bookings', 'images'])
            ->orderByDesc('bookings_count')
            ->get();

        if ($format === 'csv') {
            $filename = 'destination-statistics-' . now()->format('Y-m-d') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($destinations) {
                $file = fopen('php://output', 'w');

                // UTF-8 BOM for Excel compatibility
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Header row
                fputcsv($file, [
                    'ID',
                    'Nama',
                    'Slug',
                    'Kategori',
                    'Kota',
                    'Jumlah Gambar',
                    'Jumlah Booking',
                    'Rating',
                    'Status',
                    'Unggulan',
                    'Tanggal Dibuat',
                ]);

                foreach ($destinations as $destination) {
                    fputcsv($file, [
                        $destination->id,
                        $destination->name,
                        $destination->slug,
                        $destination->category?->name ?? '-',
                        $destination->city ?? '-',
                        $destination->images_count,
                        $destination->bookings_count,
                        number_format((float) ($destination->rating ?? 0), 1),
                        $destination->is_active ? 'Aktif' : 'Draft',
                        $destination->is_featured ? 'Ya' : 'Tidak',
                        $destination->created_at->format('d/m/Y H:i'),
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return back()->with('error', 'Format tidak didukung');
    }
}
