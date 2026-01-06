<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
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

        $logs = $query->paginate(20);

        // Transform logs for Vue
        $logs->getCollection()->transform(fn($log) => [
            'id' => $log->id,
            'action' => $log->action,
            'action_label' => $log->action_label,
            'description' => $log->description,
            'model_type' => $log->model_type,
            'model_id' => $log->model_id,
            'ip_address' => $log->ip_address,
            'created_at' => $log->created_at,
            'admin' => $log->admin ? ['name' => $log->admin->name] : null,
            'user' => $log->user ? ['name' => $log->user->name] : null,
        ]);

        $actions = ActivityLog::ACTIONS;

        $filters = $request->only(['action', 'from_date', 'to_date', 'model_type']);

        return Inertia::render('Admin/ActivityLogs/Index', [
            'logs' => $logs,
            'actions' => $actions,
            'filters' => $filters,
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
                'subject_type' => $activityLog->subject_type,
                'subject_id' => $activityLog->subject_id,
                'properties' => $activityLog->properties,
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

        ActivityLog::where('created_at', '<', now()->subDays($days))->delete();

        return redirect()->route('admin.activity-logs.index')
            ->with('success', "Log lebih dari {$days} hari berhasil dihapus.");
    }
}
