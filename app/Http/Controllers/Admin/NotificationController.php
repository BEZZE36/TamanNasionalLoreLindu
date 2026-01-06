<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index()
    {
        $admin = auth('admin')->user();
        $notifications = $admin->notifications()->paginate(20);

        // Transform for Vue
        $notifications->getCollection()->transform(fn($notification) => [
            'id' => $notification->id,
            'data' => $notification->data,
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at,
        ]);

        return Inertia::render('Admin/Notifications/Index', [
            'notifications' => $notifications
        ]);
    }

    // API: Get notifications for dropdown
    public function apiIndex()
    {
        $admin = auth('admin')->user();

        // Return empty response if not authenticated (e.g., on login page)
        if (!$admin) {
            return response()->json([
                'notifications' => [],
                'unread_count' => 0,
            ]);
        }

        $notifications = $admin->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notifikasi',
                    'message' => $notification->data['message'] ?? '',
                    'icon' => $notification->data['icon'] ?? '📌',
                    'color' => $notification->data['color'] ?? 'bg-blue-100 text-blue-600',
                    'url' => $notification->data['url'] ?? '#',
                    'time' => $notification->created_at->diffForHumans(),
                    'read_at' => $notification->read_at,
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $admin->unreadNotifications()->count(),
        ]);
    }

    // API: Mark all as read
    public function markAllRead()
    {
        $admin = auth('admin')->user();
        $admin->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    // Mark single notification as read
    public function markAsRead($id)
    {
        $admin = auth('admin')->user();
        $notification = $admin->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    // Delete notification
    public function destroy($id)
    {
        $admin = auth('admin')->user();
        $notification = $admin->notifications()->find($id);

        if ($notification) {
            $notification->delete();
        }

        return back()->with('success', 'Notifikasi dihapus.');
    }

    // Clear all notifications
    public function clearAll()
    {
        $admin = auth('admin')->user();
        $admin->notifications()->delete();

        return back()->with('success', 'Semua notifikasi dihapus.');
    }
}
