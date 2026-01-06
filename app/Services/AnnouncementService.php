<?php

namespace App\Services;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnnouncementService
{
    /**
     * Get index statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => Announcement::count(),
            'active' => Announcement::where('is_active', true)->count(),
            'scheduled' => Announcement::where('is_active', true)
                ->where('start_at', '>', now())
                ->count(),
            'expired' => Announcement::where('is_active', true)
                ->whereNotNull('end_at')
                ->where('end_at', '<', now())
                ->count(),
        ];
    }

    /**
     * Prepare data for store/update
     */
    public function prepareData(array $validated, Request $request, ?Announcement $announcement = null): array
    {
        $validated['is_dismissible'] = $request->boolean('is_dismissible', true);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['show_countdown'] = $request->boolean('show_countdown', false);
        $validated['priority'] = $validated['priority'] ?? ($announcement ? $announcement->priority : 0) ?? 0;
        $validated['position'] = $validated['position'] ?? 'top';
        $validated['target_audience'] = $validated['target_audience'] ?? 'all';
        $validated['animation_type'] = $validated['animation_type'] ?? 'fade';

        // Convert show_days to integers
        if (isset($validated['show_days'])) {
            $validated['show_days'] = array_map('intval', $validated['show_days']);
        }

        // Handle empty arrays
        if (!$request->has('target_pages')) {
            $validated['target_pages'] = null;
        }
        if (!$request->has('show_days')) {
            $validated['show_days'] = null;
        }

        return $validated;
    }

    /**
     * Handle image upload
     */
    public function handleImageUpload(Request $request, ?Announcement $announcement = null): ?string
    {
        if (!$request->hasFile('image')) {
            return null;
        }

        // Delete old image if updating
        if ($announcement && $announcement->image_path) {
            Storage::disk('public')->delete($announcement->image_path);
        }

        return $request->file('image')->store('announcements', 'public');
    }

    /**
     * Deactivate other announcements of same type
     */
    public function deactivateOthers(Announcement $announcement): void
    {
        Announcement::where('type', $announcement->type)
            ->where('is_active', true)
            ->where('id', '!=', $announcement->id)
            ->update(['is_active' => false]);
    }

    /**
     * Get validation rules
     */
    public function getValidationRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'notification_type' => 'required|in:info,success,warning,danger',
            'type' => 'required|in:banner,fullscreen',
            'bg_color' => 'nullable|string|max:20',
            'text_color' => 'nullable|string|max:20',
            'link_url' => 'nullable|url|max:500',
            'link_text' => 'nullable|string|max:100',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date',
            'is_dismissible' => 'boolean',
            'is_active' => 'boolean',
            'priority' => 'nullable|integer|min:0|max:100',
            'position' => 'nullable|in:top,bottom,floating',
            'target_audience' => 'nullable|in:all,guest,authenticated',
            'target_pages' => 'nullable|array',
            'show_days' => 'nullable|array',
            'show_time_start' => 'nullable|date_format:H:i',
            'show_time_end' => 'nullable|date_format:H:i',
            'animation_type' => 'nullable|string|max:50',
            'image' => 'nullable|image|max:2048',
            'show_countdown' => 'boolean',
            'countdown_label' => 'nullable|string|max:100',
        ];
    }

    /**
     * Duplicate announcement
     */
    public function duplicate(Announcement $announcement): Announcement
    {
        $new = $announcement->replicate();
        $new->title = $announcement->title . ' (Copy)';
        $new->is_active = false;
        $new->created_by = Auth::guard('admin')->id();
        $new->save();

        return $new;
    }

    /**
     * Get announcement statistics for statistics page
     */
    public function getPageStatistics(): array
    {
        $announcements = Announcement::orderByDesc('view_count')->get();

        $totalViews = $announcements->sum('view_count');
        $totalClicks = $announcements->sum('click_count');
        $totalDismisses = $announcements->sum('dismiss_count');

        return [
            'announcements' => $announcements,
            'totalViews' => $totalViews,
            'totalClicks' => $totalClicks,
            'totalDismisses' => $totalDismisses,
            'avgClickRate' => $totalViews > 0 ? round(($totalClicks / $totalViews) * 100, 2) : 0,
            'avgDismissRate' => $totalViews > 0 ? round(($totalDismisses / $totalViews) * 100, 2) : 0,
        ];
    }
}
