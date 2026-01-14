<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Models\Destination;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', '30'); // days
        $endDate = now();
        $startDate = now()->subDays((int) $period);
        $compareStartDate = now()->subDays((int) $period * 2);

        // Define paid statuses - using correct statuses from Booking model
        // Status 'paid' doesn't exist, 'confirmed' = paid, 'used' = ticket used
        $paidStatuses = ['confirmed', 'used'];

        // Stats with growth - Revenue from successful payments
        $currRevenue = Payment::where('status', 'success')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('gross_amount');
        $prevRevenue = Payment::where('status', 'success')
            ->whereBetween('created_at', [$compareStartDate, $startDate])
            ->sum('gross_amount');

        // If no payment records, calculate from bookings with confirmed/used status
        if ($currRevenue == 0) {
            $currRevenue = Booking::whereIn('status', $paidStatuses)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_amount');
        }
        if ($prevRevenue == 0) {
            $prevRevenue = Booking::whereIn('status', $paidStatuses)
                ->whereBetween('created_at', [$compareStartDate, $startDate])
                ->sum('total_amount');
        }

        $currBookings = Booking::whereBetween('created_at', [$startDate, $endDate])->count();
        $prevBookings = Booking::whereBetween('created_at', [$compareStartDate, $startDate])->count();

        $currVisitors = Booking::whereIn('status', $paidStatuses)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_visitors');
        $prevVisitors = Booking::whereIn('status', $paidStatuses)
            ->whereBetween('created_at', [$compareStartDate, $startDate])
            ->sum('total_visitors');

        $stats = [
            'revenue' => [
                'current' => $currRevenue,
                'growth' => $this->calculateGrowth($currRevenue, $prevRevenue)
            ],
            'bookings' => [
                'current' => $currBookings,
                'growth' => $this->calculateGrowth($currBookings, $prevBookings)
            ],
            'visitors' => [
                'current' => $currVisitors,
                'growth' => $this->calculateGrowth($currVisitors, $prevVisitors)
            ],
            'conversion_rate' => $this->calculateConversionRate($startDate, $paidStatuses),
            'avg_order_value' => $this->calculateAvgOrderValue($startDate, $paidStatuses),
        ];

        // Charts Data
        $revenueChart = $this->getRevenueChartData($period, $paidStatuses);
        $bookingsChart = $this->getDailyData(Booking::class, 'id', $period, 'count');

        // Payment Methods Distribution from payments table
        $paymentMethods = Payment::where('status', 'success')
            ->where('created_at', '>=', $startDate)
            ->select('payment_type', DB::raw('count(*) as count'), DB::raw('sum(gross_amount) as total'))
            ->groupBy('payment_type')
            ->get()
            ->map(function ($pm) {
                return [
                    'payment_type' => $pm->payment_type ?: 'Lainnya',
                    'count' => $pm->count,
                    'total' => $pm->total ?? 0
                ];
            });

        // If no payment records, get payment info from bookings
        if ($paymentMethods->isEmpty()) {
            $bookingPayments = Booking::whereIn('status', $paidStatuses)
                ->where('created_at', '>=', $startDate)
                ->selectRaw("CASE WHEN total_amount = 0 THEN 'Gratis' ELSE 'Pembayaran' END as payment_type")
                ->selectRaw('count(*) as count')
                ->selectRaw('sum(total_amount) as total')
                ->groupBy(DB::raw("CASE WHEN total_amount = 0 THEN 'Gratis' ELSE 'Pembayaran' END"))
                ->get()
                ->map(function ($pm) {
                    return [
                        'payment_type' => $pm->payment_type,
                        'count' => $pm->count,
                        'total' => $pm->total ?? 0
                    ];
                });
            $paymentMethods = $bookingPayments;
        }

        // Top Destinations with proper revenue calculation
        $topDestinations = Destination::withCount([
            'bookings' => function ($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            }
        ])
            ->get()
            ->map(function ($dest) use ($startDate, $paidStatuses) {
                // Calculate revenue from confirmed/used bookings
                $revenue = Booking::where('destination_id', $dest->id)
                    ->whereIn('status', $paidStatuses)
                    ->where('created_at', '>=', $startDate)
                    ->sum('total_amount');

                return [
                    'id' => $dest->id,
                    'name' => $dest->name,
                    'primary_image_url' => $dest->primary_image_url,
                    'bookings_count' => $dest->bookings_count ?? 0,
                    'revenue' => (float) $revenue,
                ];
            })
            ->sortByDesc('bookings_count')
            ->take(5)
            ->values();

        $recentBookings = Booking::with(['user', 'destination'])
            ->latest()
            ->take(8)
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'user' => $b->user ? ['name' => $b->user->name] : null,
                'destination' => $b->destination ? ['name' => $b->destination->name] : null,
                'total_amount' => $b->total_amount,
                'status' => $b->status,
                'created_at_human' => $b->created_at->diffForHumans(),
            ]);

        // Calculate total for payment methods percentage
        $totalPaymentRevenue = $paymentMethods->sum('total');

        return Inertia::render('Admin/Analytics/Index', compact(
            'stats',
            'revenueChart',
            'bookingsChart',
            'paymentMethods',
            'topDestinations',
            'recentBookings',
            'period',
            'totalPaymentRevenue'
        ));
    }

    public function export(Request $request)
    {
        $startDate = $request->get('from_date', now()->subDays(30));
        $payments = Payment::with(['booking.user', 'booking.destination'])
            ->where('status', 'success')
            ->whereDate('created_at', '>=', $startDate)
            ->latest()
            ->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=revenue-report-" . date('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Date', 'Order ID', 'Customer', 'Destination', 'Amount', 'Payment Method', 'Status'];

        $callback = function () use ($payments, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->created_at->format('Y-m-d H:i'),
                    $payment->order_id,
                    $payment->booking->user->name ?? 'Guest',
                    $payment->booking->destination->name ?? '-',
                    $payment->gross_amount,
                    $payment->payment_type,
                    $payment->status
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function calculateGrowth($current, $previous)
    {
        if ($previous == 0)
            return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function calculateConversionRate($startDate, $paidStatuses)
    {
        $total = Booking::where('created_at', '>=', $startDate)->count();
        $paid = Booking::whereIn('status', $paidStatuses)->where('created_at', '>=', $startDate)->count();

        return $total > 0 ? round(($paid / $total) * 100, 1) : 0;
    }

    private function calculateAvgOrderValue($startDate, $paidStatuses)
    {
        // Try from payments first
        $paymentAvg = Payment::where('status', 'success')
            ->where('created_at', '>=', $startDate)
            ->avg('gross_amount');

        if ($paymentAvg && $paymentAvg > 0) {
            return $paymentAvg;
        }

        // Fallback to bookings with confirmed/used status
        $bookingAvg = Booking::whereIn('status', $paidStatuses)
            ->where('created_at', '>=', $startDate)
            ->avg('total_amount');

        return $bookingAvg ?? 0;
    }

    private function getRevenueChartData($days, $paidStatuses)
    {
        // Get payment data
        $paymentData = Payment::where('status', 'success')
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, SUM(gross_amount) as total')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Get booking data for confirmed/used bookings (as fallback or additional)
        $bookingData = Booking::whereIn('status', $paidStatuses)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $chartData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $paymentTotal = $paymentData->get($date)?->total ?? 0;
            $bookingTotal = $bookingData->get($date)?->total ?? 0;

            // Use payment data if available, otherwise use booking data
            $total = $paymentTotal > 0 ? $paymentTotal : $bookingTotal;

            $chartData[] = [
                'date' => now()->subDays($i)->format('d M'),
                'total' => (float) $total,
            ];
        }

        return $chartData;
    }

    private function getDailyData($model, $column, $days, $aggregator = 'count', $conditions = [])
    {
        $query = $model::where('created_at', '>=', now()->subDays($days));

        foreach ($conditions as $key => $value) {
            $query->where($key, $value);
        }

        $data = $query->selectRaw('DATE(created_at) as date, ' . ($aggregator === 'sum' ? "SUM($column)" : "COUNT($column)") . ' as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartData[] = [
                'date' => now()->subDays($i)->format('d M'),
                'total' => $data->get($date)?->total ?? 0,
            ];
        }

        return $chartData;
    }
}

