<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    /**
     * Export Bookings to CSV
     */
    public function bookings(Request $request)
    {
        $query = Booking::with(['user', 'destination', 'payment']);
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        $bookings = $query->latest()->get();
        
        $filename = 'bookings_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($bookings) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, [
                'No',
                'Order Number',
                'Nama Customer',
                'Email',
                'Phone',
                'Destinasi',
                'Tanggal Kunjungan',
                'Jumlah Pengunjung',
                'Total Amount',
                'Status',
                'Payment Method',
                'Tanggal Booking',
            ]);
            
            // Data rows
            foreach ($bookings as $index => $booking) {
                fputcsv($file, [
                    $index + 1,
                    $booking->order_number,
                    $booking->leader_name ?? $booking->user?->name ?? '-',
                    $booking->leader_email ?? $booking->user?->email ?? '-',
                    $booking->leader_phone ?? '-',
                    $booking->destination?->name ?? '-',
                    $booking->visit_date?->format('d/m/Y') ?? '-',
                    $booking->total_visitors ?? 0,
                    $booking->total_amount ?? 0,
                    ucfirst($booking->status ?? '-'),
                    $booking->payment?->payment_type ?? '-',
                    $booking->created_at?->format('d/m/Y H:i') ?? '-',
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Users to CSV
     */
    public function users(Request $request)
    {
        $query = User::withCount('bookings');
        
        // Apply filters
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        $users = $query->latest()->get();
        
        $filename = 'users_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, [
                'No',
                'ID',
                'Nama',
                'Email',
                'Phone',
                'Total Bookings',
                'Status',
                'Tanggal Registrasi',
            ]);
            
            // Data rows
            foreach ($users as $index => $user) {
                fputcsv($file, [
                    $index + 1,
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone ?? '-',
                    $user->bookings_count,
                    $user->is_active ? 'Aktif' : 'Nonaktif',
                    $user->created_at?->format('d/m/Y H:i') ?? '-',
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Revenue Report to CSV
     */
    public function revenue(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->format('Y-m-d');
        $toDate = $request->to_date ?? now()->format('Y-m-d');
        
        $payments = \App\Models\Payment::where('status', 'success')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->with('booking.destination')
            ->latest()
            ->get();
        
        $filename = 'revenue_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'No',
                'Transaction ID',
                'Order Number',
                'Destinasi',
                'Amount',
                'Payment Method',
                'Status',
                'Tanggal',
            ]);
            
            $total = 0;
            foreach ($payments as $index => $payment) {
                $total += $payment->gross_amount;
                fputcsv($file, [
                    $index + 1,
                    $payment->transaction_id ?? '-',
                    $payment->booking?->order_number ?? '-',
                    $payment->booking?->destination?->name ?? '-',
                    $payment->gross_amount,
                    $payment->payment_type ?? '-',
                    ucfirst($payment->status),
                    $payment->created_at?->format('d/m/Y H:i') ?? '-',
                ]);
            }
            
            // Total row
            fputcsv($file, ['', '', '', 'TOTAL', $total, '', '', '']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
