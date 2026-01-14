<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class PublicAnnouncementController extends Controller
{
    /**
     * Get active announcements for the frontend.
     */
    public function index()
    {
        $announcements = Announcement::active()
            ->latest()
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
                    'id' => $item->id,
                    'title' => $item->title,
                    'message' => $item->message,
                    'type' => $item->type,
                    'bg_color' => $item->bg_color,
                    'text_color' => $item->text_color,
                    'link_url' => $item->link_url,
                    'link_text' => $item->link_text,
                    'is_dismissible' => $item->is_dismissible,
                    'created_at' => $item->created_at->diffForHumans(),
                    'color_class' => $colorClass,
                    'icon' => $icon,
                ];
            });

        return response()->json([
            'count' => $announcements->count(),
            'announcements' => $announcements
        ]);
    }
}
