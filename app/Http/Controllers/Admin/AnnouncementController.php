<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    use LogsActivity;

    public function __construct(
        protected AnnouncementService $service
    ) {
    }

    public function index(Request $request)
    {
        $query = Announcement::with('creator');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $announcements = $query->latest()->paginate(12)->withQueryString();
        $announcements->getCollection()->transform(fn($a) => [
            'id' => $a->id,
            'title' => $a->title,
            'message' => $a->message,
            'type' => $a->type,
            'is_active' => $a->is_active,
            'bg_color' => $a->bg_color,
            'view_count' => $a->view_count,
        ]);

        $stats = $this->service->getStatistics();

        return Inertia::render('Admin/Announcements/Index', [
            'announcements' => $announcements,
            'stats' => $stats,
            'filters' => $request->only(['search', 'type', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Announcements/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->service->getValidationRules());

        $data = $this->service->prepareData($validated, $request);
        $data['created_by'] = Auth::guard('admin')->id();

        if ($imagePath = $this->service->handleImageUpload($request)) {
            $data['image_path'] = $imagePath;
        }
        unset($data['image']);

        if ($data['is_active']) {
            Announcement::where('type', $data['type'])->where('is_active', true)->update(['is_active' => false]);
        }

        $announcement = Announcement::create($data);

        $this->logCreate($announcement, 'Pengumuman', $announcement->title);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function edit(Announcement $announcement)
    {
        return Inertia::render('Admin/Announcements/Edit', [
            'announcement' => [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'message' => $announcement->message,
                'type' => $announcement->type,
                'notification_type' => $announcement->notification_type,
                'bg_color' => $announcement->bg_color,
                'text_color' => $announcement->text_color,
                'link_url' => $announcement->link_url,
                'link_text' => $announcement->link_text,
                'is_dismissible' => $announcement->is_dismissible,
                'is_active' => $announcement->is_active,
                'start_date' => optional($announcement->start_date)->toIso8601String(),
                'end_date' => optional($announcement->end_date)->toIso8601String(),
            ],
        ]);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate($this->service->getValidationRules());

        $oldValues = $announcement->only(['title', 'message', 'type', 'is_active']);

        $data = $this->service->prepareData($validated, $request, $announcement);

        if ($imagePath = $this->service->handleImageUpload($request, $announcement)) {
            $data['image_path'] = $imagePath;
        }
        unset($data['image']);

        if ($data['is_active']) {
            $this->service->deactivateOthers($announcement);
        }

        $announcement->update($data);

        $newValues = $announcement->only(['title', 'message', 'type', 'is_active']);
        $this->logUpdate($announcement, 'Pengumuman', $oldValues, $newValues, $announcement->title);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        $title = $announcement->title;
        $this->logDelete($announcement, 'Pengumuman', $title);

        $announcement->delete();
        return redirect()->route('admin.announcements.index')->with('success', 'Pengumuman berhasil dihapus.');
    }

    public function toggleStatus(Announcement $announcement)
    {
        $newStatus = !$announcement->is_active;

        if ($newStatus) {
            $this->service->deactivateOthers($announcement);
        }

        $announcement->update(['is_active' => $newStatus]);

        $this->logToggle($announcement, 'Pengumuman', 'is_active', $announcement->is_active, $announcement->title);

        return response()->json([
            'success' => true,
            'is_active' => $announcement->is_active,
            'message' => $announcement->is_active ? 'Pengumuman diaktifkan.' : 'Pengumuman dinonaktifkan'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:announcements,id']);
        $count = Announcement::whereIn('id', $request->ids)->delete();

        $this->logBulk('delete', 'Pengumuman', $count);

        return response()->json(['success' => true, 'message' => "{$count} pengumuman berhasil dihapus"]);
    }

    public function duplicate(Announcement $announcement)
    {
        $new = $this->service->duplicate($announcement);

        $this->logCreate($new, 'Pengumuman (Duplikat)', $new->title);

        return redirect()->route('admin.announcements.edit', $new)->with('success', 'Pengumuman berhasil diduplikasi.');
    }

    public function preview(Announcement $announcement)
    {
        return response()->json($announcement->only([
            'id',
            'title',
            'message',
            'notification_type',
            'type',
            'bg_color',
            'text_color',
            'link_url',
            'link_text',
            'is_dismissible'
        ]));
    }

    public function trackView(Announcement $announcement)
    {
        $announcement->incrementView();
        return response()->json(['success' => true]);
    }

    public function trackClick(Announcement $announcement)
    {
        $announcement->incrementClick();
        return response()->json(['success' => true]);
    }

    public function trackDismiss(Announcement $announcement)
    {
        $announcement->incrementDismiss();
        return response()->json(['success' => true]);
    }

    public function statistics()
    {
        $stats = $this->service->getPageStatistics();
        $stats['announcements'] = collect($stats['announcements'])->map(fn($a) => [
            'id' => $a->id,
            'title' => $a->title,
            'type' => $a->type,
            'view_count' => $a->view_count,
            'click_count' => $a->click_count,
            'dismiss_count' => $a->dismiss_count,
        ])->toArray();
        return Inertia::render('Admin/Announcements/Statistics', $stats);
    }

    public function export()
    {
        $this->logExport('Pengumuman', 'JSON');

        return response()->json(Announcement::all())
            ->header('Content-Disposition', 'attachment; filename="announcements-export-' . date('Y-m-d') . '.json"');
    }
}
