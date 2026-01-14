<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Payment;
use App\Models\VisitorLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(\Illuminate\Http\Request $request, \App\Services\SystemHealthService $healthService)
    {
        // 0. Date Filter Logic
        $reqStart = $request->get('start_date');
        $reqEnd = $request->get('end_date');

        if ($reqStart && $reqEnd) {
            $startDate = Carbon::parse($reqStart)->startOfDay();
            $endDate = Carbon::parse($reqEnd)->endOfDay();
            $periodLabel = $startDate->translatedFormat('d M') . ' - ' . $endDate->translatedFormat('d M');
            if ($startDate->isSameDay($endDate)) {
                $periodLabel = $startDate->translatedFormat('d M Y');
            }
        } else {
            // Default: Last 7 Days
            $startDate = now()->subDays(6)->startOfDay();
            $endDate = now()->endOfDay();
            $periodLabel = '7 Hari Terakhir';
        }

        $isSingleDay = $startDate->isSameDay($endDate);

        // 1. Basic Stats (Filtered)
        $stats = [
            'totalBookings' => Booking::count(), // Always total
            'periodBookings' => Booking::whereBetween('created_at', [$startDate, $endDate])->count(),
            'todayBookings' => Booking::whereDate('created_at', today())->count(),
            'pendingBookings' => Booking::where('status', 'pending')->count(),
            'totalRevenue' => Payment::where('status', 'success')->sum('gross_amount') ?? 0,
            'periodRevenue' => Payment::where('status', 'success')->whereBetween('created_at', [$startDate, $endDate])->sum('gross_amount') ?? 0,
            'monthRevenue' => Payment::where('status', 'success')->whereMonth('created_at', now()->month)->sum('gross_amount') ?? 0,
            'totalUsers' => User::count(),
            'totalDestinations' => Destination::count(),
            'totalFlora' => \App\Models\Flora::count(),
            'totalFauna' => \App\Models\Fauna::count(),
            'totalGallery' => \App\Models\Gallery::count(),
            'totalVisitors' => VisitorLog::count(),
            'periodVisitors' => VisitorLog::whereBetween('created_at', [$startDate, $endDate])->count(),
        ];

        // 2. Revenue & Visitor Trend (Dynamic: Hourly if Single Day, Daily otherwise)
        $labels = [];
        $revenueData = [];
        $visitorData = [];

        if ($isSingleDay) {
            // Hourly Graph (00:00 - 23:00)
            $revenueTrendRaw = Payment::where('status', 'success')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('HOUR(created_at) as hour, SUM(gross_amount) as total')
                ->groupBy('hour')
                ->pluck('total', 'hour');

            $visitorTrendRaw = VisitorLog::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                ->groupBy('hour')
                ->pluck('count', 'hour');

            for ($i = 0; $i <= 23; $i++) {
                $labels[] = sprintf('%02d:00', $i);
                $revenueData[] = $revenueTrendRaw[$i] ?? 0;
                $visitorData[] = $visitorTrendRaw[$i] ?? 0;
            }
        } else {
            // Daily Graph
            $revenueTrendRaw = Payment::where('status', 'success')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, SUM(gross_amount) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            $visitorTrendRaw = VisitorLog::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date');

            $chartPeriod = \Carbon\CarbonPeriod::create($startDate, $endDate);
            foreach ($chartPeriod as $date) {
                $formattedDate = $date->format('Y-m-d');
                $labels[] = $date->format('d M');
                $revenueData[] = $revenueTrendRaw[$formattedDate] ?? 0;
                $visitorData[] = $visitorTrendRaw[$formattedDate] ?? 0;
            }
        }

        // 3. Booking Status Distribution (Filtered)
        $bookingStatus = Booking::selectRaw('status, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // 4. Top Destinations (Filtered by Period)
        $topDestinations = Destination::withCount([
            'bookings' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        ])
            ->orderByDesc('bookings_count')
            ->take(5)
            ->get();

        // 5. Recent Bookings (Standard)
        $recentBookings = Booking::with(['user', 'destination'])->latest()->take(5)->get();

        // 6. Recent Activities
        $recentActivities = \App\Models\ActivityLog::with(['user', 'admin'])
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($log) {
                return [
                    'title' => $log->action_label,
                    'description' => $log->description,
                    'time' => $log->created_at,
                    'color' => explode('-', $log->color_class)[1] ?? 'gray',
                    'icon' => $log->icon
                ];
            });

        // 7. Monthly Goals (Static for current month)
        $monthlyBookingGoal = 100;
        $monthlyRevenueGoal = 5000000;
        $monthlyBookingsCount = Booking::whereMonth('created_at', now()->month)->count();

        $goals = [
            'booking_progress' => min(100, round(($monthlyBookingsCount / $monthlyBookingGoal) * 100)),
            'revenue_progress' => min(100, round(($stats['monthRevenue'] / $monthlyRevenueGoal) * 100)),
            'monthly_bookings' => $monthlyBookingsCount,
            'monthly_revenue_goal' => $monthlyRevenueGoal,
            'monthly_booking_goal' => $monthlyBookingGoal
        ];

        // 8. System Status (Real)
        $systemStatus = $healthService->getSystemStatus();

        // 9. Need Attention
        $attention = [
            'pending_bookings' => $stats['pendingBookings'],
            'unread_feedback' => \App\Models\Feedback::where('status', 'unread')->count() ?? 0,
            'today_users' => User::whereDate('created_at', today())->count(),
        ];

        // 10. Mini Calendar Data (Current Month)
        $calendarBookings = Booking::selectRaw('DATE(visit_date) as date, COUNT(*) as count')
            ->whereMonth('visit_date', now()->month)
            ->whereYear('visit_date', now()->year)
            ->groupBy('date')
            ->pluck('count', 'date');

        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => $stats,
            'labels' => $labels,
            'revenueData' => $revenueData,
            'visitorData' => $visitorData,
            'bookingStatus' => $bookingStatus,
            'topDestinations' => $topDestinations,
            'recentBookings' => $recentBookings,
            'recentActivities' => $recentActivities,
            'goals' => $goals,
            'systemStatus' => $systemStatus,
            'attention' => $attention,
            'calendarBookings' => $calendarBookings,
            'periodLabel' => $periodLabel,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);
    }
}
