<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display all notifications for the user.
     */
    public function index()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Append computed attributes for each notification
        $notifications->getCollection()->each(function ($notification) {
            $notification->append(['icon_emoji', 'color_class', 'time_ago']);
        });

        return \Inertia\Inertia::render('User/Notifications/Index', compact('notifications'));
    }

    /**
     * Get recent notifications for dropdown (AJAX).
     */
    public function getRecent()
    {
        // 1. Get User Notifications
        $userNotifications = Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => 'notification', // Add type differentiator
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'icon' => $notification->icon_emoji,
                    'color_class' => $notification->color_class,
                    'link' => $notification->link,
                    'is_read' => $notification->is_read,
                    'time_ago' => $notification->time_ago,
                    'timestamp' => $notification->created_at->timestamp, // For sorting
                ];
            });

        // 2. Get Active Announcements
        $announcements = \App\Models\Announcement::active()
            ->latest()
            ->take(5) // Limit announcements to keep relevant
            ->get()
            ->map(function ($item) {
                // Determine icon and color based on notification_type
                $icon = match ($item->notification_type) {
                    'success' => '✅',
                    'warning' => '⚠️',
                    'danger' => '🛑',
                    'info' => 'ℹ️',
                    default => '📢'
                };

                $colorClass = match ($item->notification_type) {
                    'success' => 'bg-green-100 text-green-600 border-green-200',
                    'warning' => 'bg-yellow-100 text-yellow-600 border-yellow-200',
                    'danger' => 'bg-red-100 text-red-600 border-red-200',
                    'info' => 'bg-blue-100 text-blue-600 border-blue-200',
                    default => 'bg-primary-50 text-primary-600 border-primary-200'
                };

                return [
                    'id' => 'ann_' . $item->id, // Prefix ID to avoid collision
                    'type' => 'announcement',
                    'title' => $item->title,
                    'message' => $item->message,
                    'icon' => $icon,
                    'color_class' => $colorClass,
                    'link' => $item->link_url ?? '#',
                    'is_read' => false, // Auth users: Client-side tracking or always new? Let's use false for high visibility
                    'time_ago' => $item->created_at->diffForHumans(),
                    'timestamp' => $item->created_at->timestamp,
                ];
            });

        // 3. Merge & Sort
        $merged = $userNotifications->concat($announcements)
            ->sortByDesc('timestamp')
            ->values()
            ->take(10); // Limit total

        $unreadCount = Notification::getUnreadCountForUser(auth()->id());
        // Note: We're not adding announcement count to unreadCount server-side 
        // because we don't track their read state in DB. 
        // Frontend can add to it if desired based on local storage.

        return response()->json([
            'notifications' => $merged,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Get unread notification count (AJAX).
     */
    public function getUnreadCount()
    {
        $count = Notification::getUnreadCountForUser(auth()->id());

        return response()->json(['count' => $count]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $notification->markAsRead();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        // Redirect to the notification link if available
        if ($notification->link) {
            return redirect($notification->link);
        }

        return back();
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        $count = Notification::markAllAsReadForUser(auth()->id());

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'marked_count' => $count,
            ]);
        }

        return back()->with('success', "{$count} notifikasi telah ditandai sudah dibaca.");
    }

    /**
     * Delete a notification.
     */
    public function destroy($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $notification->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }

    /**
     * Clear all notifications.
     */
    public function clearAll()
    {
        Notification::where('user_id', auth()->id())->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi berhasil dihapus.');
    }
}
