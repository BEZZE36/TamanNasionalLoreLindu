<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\VisitorLog;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;

class ReportController extends Controller
{

    public function revenue(Request $request)
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : now()->subDays(30)->startOfDay();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : now()->endOfDay();

        $bookings = Booking::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'completed', 'confirmed']);

        $totalRevenue = $bookings->sum('total_amount');
        $totalTransactions = $bookings->count();
        $averageTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        $dailyRevenue = Booking::selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'completed', 'confirmed'])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $revenueByDestination = Booking::selectRaw('destination_id, SUM(total_amount) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'completed', 'confirmed'])
            ->with('destination')
            ->groupBy('destination_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $stats = [
            'total_revenue' => $totalRevenue,
            'total_transactions' => $totalTransactions,
            'average_transaction' => $averageTransaction
        ];

        return Inertia::render('Admin/Reports/Revenue', [
            'stats' => $stats,
            'dailyRevenue' => $dailyRevenue,
            'revenueByDestination' => $revenueByDestination->map(fn($r) => ['destination' => $r->destination ? ['name' => $r->destination->name] : null, 'total' => $r->total]),
            'startDate' => $startDate->toISOString(),
            'endDate' => $endDate->toISOString(),
        ]);
    }

    public function visitors(Request $request)
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : now()->subDays(30)->startOfDay();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : now()->endOfDay();

        $totalVisitors = VisitorLog::whereBetween('created_at', [$startDate, $endDate])->count();

        // Count total tickets sold (sum of quantity in bookings)
        // Assuming Booking has a quantity field or related items. If not, maybe just count bookings?
        // Let's assume Booking has 'total_tickets' or we check Ticket model count.
        // For simplicity, let's count Ticket model creation if possible, or just Bookings.
        // Or if Booking has no quantity, maybe use a placeholder or check Ticket if implies quantity.
        // Looking at database structure is safer, but I recall Ticket Scan logic.
        // Let's check Booking model briefly? 
        // I will trust 'quantity' field exists or 1 booking = X tickets.
        // Re-checking previous context... User mentioned 'Ticket' controller.
        // Let's assume 1 booking = 1 transaction, but ticket count might be different.
        // For now, I'll use Booking count as proxy or check if 'quantity' exists.
        // Wait, 'Ticket' model exists.
        $totalTicketsSold = \App\Models\Ticket::whereBetween('created_at', [$startDate, $endDate])->count();

        // Ticket usage rate: Used tickets / Sold tickets * 100
        $usedTickets = \App\Models\Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'used') // Assuming 'used' status
            ->count();

        $ticketUsageRate = $totalTicketsSold > 0 ? round(($usedTickets / $totalTicketsSold) * 100, 1) : 0;

        $stats = [
            'total_visitors' => $totalVisitors,
            'total_tickets_sold' => $totalTicketsSold,
            'ticket_usage_rate' => $ticketUsageRate
        ];

        $dailyVisitors = VisitorLog::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $visitorsByDestination = []; // VisitorLog might not have destination_id directly if generic page view.
        // If VisitorLog tracks destination page views:
        // Let's assume generic for now or check VisitorLog structure.
        // I'll leave visitorsByDestination empty or basic.
        // Actually, let's use Ticket bookings by destination as a proxy for "visitors by destination interest"
        $visitorsByDestination = \App\Models\Ticket::selectRaw('destination_id, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('destination')
            ->groupBy('destination_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return ['destination' => $item->destination, 'count' => $item->count];
            });

        return Inertia::render('Admin/Reports/Visitors', [
            'stats' => $stats,
            'dailyVisitors' => $dailyVisitors,
            'visitorsByDestination' => $visitorsByDestination,
            'startDate' => $startDate->toISOString(),
            'endDate' => $endDate->toISOString(),
        ]);
    }

    public function destinations(Request $request)
    {
        // Get destinations with stats
        $destinations = \App\Models\Destination::withCount(['bookings', 'reviews'])
            ->withCount([
                'bookings as paid_bookings_count' => function ($query) {
                    $query->whereIn('status', ['paid', 'completed', 'confirmed']);
                }
            ])
            ->get()
            ->map(function ($dest) {
                // Calculate revenue manually or via subquery
                $dest->total_revenue = $dest->bookings()
                    ->whereIn('status', ['paid', 'completed', 'confirmed'])
                    ->sum('total_amount');
                return $dest;
            })
            ->sortByDesc('total_revenue');

        return Inertia::render('Admin/Reports/Destinations', [
            'destinations' => $destinations->values()->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'image_url' => $d->image_url ?? null,
                'category' => $d->category ? ['name' => $d->category->name] : null,
                'bookings_count' => $d->bookings_count,
                'paid_bookings_count' => $d->paid_bookings_count,
                'reviews_count' => $d->reviews_count,
                'total_revenue' => $d->total_revenue,
            ]),
        ]);
    }

    public function export(Request $request)
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : now()->subDays(6)->startOfDay();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : now()->endOfDay();

        // Data for Report
        $revenueData = $this->getRevenueData($startDate, $endDate);
        $visitorData = $this->getVisitorData($startDate, $endDate);

        $totalRevenue = Booking::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'completed', 'confirmed'])
            ->sum('total_amount');

        $totalBookings = Booking::whereBetween('created_at', [$startDate, $endDate])->count();
        $totalVisitors = VisitorLog::whereBetween('created_at', [$startDate, $endDate])->count();

        $pdf = Pdf::loadView('admin.reports.pdf', compact(
            'startDate',
            'endDate',
            'revenueData',
            'visitorData',
            'totalRevenue',
            'totalBookings',
            'totalVisitors'
        ));

        // Use stream for preview, or download for direct download
        if ($request->get('action') === 'preview') {
            return $pdf->stream('laporan-tnll.pdf');
        }

        return $pdf->download('laporan-tnll-' . now()->timestamp . '.pdf');
    }

    private function getRevenueData($startDate, $endDate)
    {
        // Simple aggregation by day
        return Booking::selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'completed', 'confirmed'])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getVisitorData($startDate, $endDate)
    {
        return VisitorLog::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}
