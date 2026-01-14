<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with(['admin', 'user'])->latest();

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Filter by model type
        if ($request->filled('model_type')) {
            $query->where('model_type', 'like', '%' . $request->model_type . '%');
        }

        // Search by description
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $logs = $query->paginate(25);

        // Transform logs for Vue
        $logs->getCollection()->transform(fn($log) => [
            'id' => $log->id,
            'action' => $log->action,
            'action_label' => $log->action_label,
            'description' => $log->description,
            'model_type' => $log->model_type,
            'model_id' => $log->model_id,
            'old_values' => $log->old_values,
            'new_values' => $log->new_values,
            'ip_address' => $log->ip_address,
            'user_agent' => $log->user_agent,
            'created_at' => $log->created_at,
            'admin' => $log->admin ? ['id' => $log->admin->id, 'name' => $log->admin->name] : null,
            'user' => $log->user ? ['id' => $log->user->id, 'name' => $log->user->name] : null,
        ]);

        $actions = ActivityLog::ACTIONS;

        $filters = $request->only(['action', 'from_date', 'to_date', 'model_type', 'search']);

        // Stats
        $stats = [
            'total' => ActivityLog::count(),
            'today' => ActivityLog::whereDate('created_at', today())->count(),
            'this_week' => ActivityLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'create' => ActivityLog::where('action', 'create')->count(),
            'update' => ActivityLog::where('action', 'update')->count(),
            'delete' => ActivityLog::where('action', 'delete')->count(),
        ];

        // Get unique model types for filter dropdown
        $modelTypes = ActivityLog::distinct()
            ->whereNotNull('model_type')
            ->pluck('model_type')
            ->map(fn($type) => [
                'value' => $type,
                'label' => class_basename($type),
            ])
            ->values();

        return Inertia::render('Admin/ActivityLogs/Index', [
            'logs' => $logs,
            'actions' => $actions,
            'filters' => $filters,
            'stats' => $stats,
            'modelTypes' => $modelTypes,
        ]);
    }

    public function show(ActivityLog $activityLog)
    {
        $activityLog->load(['admin', 'user']);
        return Inertia::render('Admin/ActivityLogs/Show', [
            'activityLog' => [
                'id' => $activityLog->id,
                'description' => $activityLog->description,
                'action' => $activityLog->action,
                'action_label' => $activityLog->action_label,
                'model_type' => $activityLog->model_type,
                'model_id' => $activityLog->model_id,
                'old_values' => $activityLog->old_values,
                'new_values' => $activityLog->new_values,
                'ip_address' => $activityLog->ip_address,
                'user_agent' => $activityLog->user_agent,
                'created_at' => $activityLog->created_at,
                'admin' => $activityLog->admin ? ['name' => $activityLog->admin->name, 'email' => $activityLog->admin->email] : null,
                'user' => $activityLog->user ? ['name' => $activityLog->user->name, 'email' => $activityLog->user->email] : null,
            ]
        ]);
    }

    public function destroy(ActivityLog $activityLog)
    {
        $activityLog->delete();
        return redirect()->route('admin.activity-logs.index')
            ->with('success', 'Log berhasil dihapus.');
    }

    public function clear(Request $request)
    {
        $days = $request->get('days', 30);

        $count = ActivityLog::where('created_at', '<', now()->subDays($days))->count();
        ActivityLog::where('created_at', '<', now()->subDays($days))->delete();

        return redirect()->route('admin.activity-logs.index')
            ->with('success', "Berhasil menghapus {$count} log yang lebih dari {$days} hari.");
    }

    public function export(Request $request)
    {
        $query = ActivityLog::with(['admin', 'user'])->latest();

        // Apply same filters as index
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        if ($request->filled('model_type')) {
            $query->where('model_type', 'like', '%' . $request->model_type . '%');
        }

        $logs = $query->get();

        // Generate CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="activity_logs_' . now()->format('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, ['ID', 'Waktu', 'Aksi', 'Deskripsi', 'Model', 'Model ID', 'User/Admin', 'IP Address']);

            foreach ($logs as $log) {
                $performer = $log->admin ? $log->admin->name . ' (Admin)' : ($log->user ? $log->user->name : 'System');
                fputcsv($file, [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->action_label,
                    $log->description,
                    $log->model_type ? class_basename($log->model_type) : '-',
                    $log->model_id ?? '-',
                    $performer,
                    $log->ip_address ?? '-',
                ]);
            }

            fclose($file);
        };

        // Log export action
        ActivityLog::log('export', 'Mengekspor ' . $logs->count() . ' activity logs ke CSV');

        return Response::stream($callback, 200, $headers);
    }
}
