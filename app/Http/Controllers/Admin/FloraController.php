<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flora;
use App\Models\FloraImage;
use App\Services\Admin\FloraService;
use App\Services\NotificationService;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FloraController extends Controller
{
    protected FloraService $floraService;
    protected NotificationService $notificationService;
    protected PushNotificationService $pushNotificationService;

    public function __construct(
        FloraService $floraService,
        NotificationService $notificationService,
        PushNotificationService $pushNotificationService
    ) {
        $this->floraService = $floraService;
        $this->notificationService = $notificationService;
        $this->pushNotificationService = $pushNotificationService;
    }

    public function dashboard(Request $request)
    {
        $period = $request->get('period', 30);
        $startDate = now()->subDays($period);

        $stats = [
            'total' => Flora::count(),
            'published' => Flora::where('is_active', true)->count(),
            'draft' => Flora::where('is_active', false)->count(),
            'featured' => Flora::where('is_featured', true)->count(),
            'endangered' => Flora::where('category', 'langka')->count(),
            'endemic' => Flora::where('category', 'endemik')->count(),
            'total_views' => Flora::sum('view_count'),
            'total_comments' => \App\Models\FloraComment::count(),
        ];

        // Generate viewsChart data based on created_at dates
        $viewsChart = collect(range(0, $period - 1))->map(function ($i) use ($startDate) {
            $date = $startDate->copy()->addDays($i);
            return [
                'date' => $date->format('d M'),
                'views' => Flora::whereDate('created_at', $date)->sum('view_count'),
            ];
        })->values()->toArray();

        $topFlora = Flora::orderBy('view_count', 'desc')
            ->take(10)
            ->get()
            ->map(fn($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'category' => $f->category,
                'views_count' => $f->view_count,
            ]);

        $recentFlora = Flora::latest()
            ->take(8)
            ->get()
            ->map(fn($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'scientific_name' => $f->scientific_name,
                'category' => $f->category,
                'image_url' => $f->image_url,
            ]);

        return \Inertia\Inertia::render('Admin/Flora/Dashboard', [
            'stats' => $stats,
            'viewsChart' => $viewsChart,
            'topFlora' => $topFlora,
            'recentFlora' => $recentFlora,
            'period' => $period,
        ]);
    }

    public function index(Request $request)
    {
        $query = Flora::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('local_name', 'like', '%' . $request->search . '%')
                    ->orWhere('scientific_name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $flora = $query->ordered()->paginate(15)->withQueryString();

        $totalFlora = Flora::count();
        $activeFlora = Flora::where('is_active', true)->count();
        $featuredFlora = Flora::where('is_featured', true)->count();
        $inactiveFlora = Flora::where('is_active', false)->count();

        $filters = ['search' => request()->search, 'category' => request()->category, 'status' => request()->status];
        $categories = Flora::CATEGORIES;

        return \Inertia\Inertia::render('Admin/Flora/Index', compact('flora', 'totalFlora', 'activeFlora', 'featuredFlora', 'inactiveFlora', 'filters', 'categories'));
    }

    public function create()
    {
        $categories = Flora::CATEGORIES;
        $conservationStatuses = Flora::CONSERVATION_STATUSES;
        return \Inertia\Inertia::render('Admin/Flora/Create', compact('categories', 'conservationStatuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:flora,slug',
            'local_name' => 'nullable|string|max:255',
            'scientific_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'category' => 'required|in:umum,langka,endemik',
            'conservation_status' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'is_featured' => 'nullable',
            'is_active' => 'nullable',
            'gallery' => 'nullable|array',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $flora = $this->floraService->createFlora($validated, $request);

        // Send notifications to all users
        if ($flora->is_active) {
            $url = route('flora.show', $flora->slug);
            $this->notificationService->notifyNewFlora(
                $flora->name,
                $flora->description ?? '',
                $url
            );
            $this->pushNotificationService->notifyNewFlora(
                $flora->name,
                $flora->description ?? '',
                $url
            );
        }

        return redirect()->route('admin.flora.index')
            ->with('success', 'Flora berhasil ditambahkan.');
    }

    public function edit(Flora $flora)
    {
        $flora->load('images');
        $categories = Flora::CATEGORIES;
        $conservationStatuses = Flora::CONSERVATION_STATUSES;
        return \Inertia\Inertia::render('Admin/Flora/Edit', compact('flora', 'categories', 'conservationStatuses'));
    }

    public function update(Request $request, Flora $flora)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:flora,slug,' . $flora->id,
            'local_name' => 'nullable|string|max:255',
            'scientific_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'category' => 'required|in:umum,langka,endemik',
            'conservation_status' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'gallery' => 'nullable|array',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $this->floraService->updateFlora($flora, $validated, $request);

        return redirect()->route('admin.flora.index')
            ->with('success', 'Flora berhasil diperbarui.');
    }

    public function destroy(Flora $flora)
    {
        if ($flora->image_path) {
            Storage::disk('public')->delete($flora->image_path);
        }
        $flora->images()->delete();
        $flora->delete();

        return redirect()->route('admin.flora.index')
            ->with('success', 'Flora berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:flora,id',
        ]);

        $count = 0;
        foreach ($request->ids as $id) {
            $flora = Flora::find($id);
            if ($flora) {
                if ($flora->image_path) {
                    Storage::disk('public')->delete($flora->image_path);
                }
                $flora->images()->delete();
                $flora->delete();
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$count} flora berhasil dihapus.",
        ]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:flora,id',
        ]);

        foreach ($request->order as $index => $id) {
            Flora::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function toggleActive(Flora $flora)
    {
        $flora->update(['is_active' => !$flora->is_active]);
        return response()->json([
            'success' => true,
            'message' => 'Status flora berhasil diperbarui.',
            'is_active' => $flora->is_active
        ]);
    }

    public function toggleFeatured(Flora $flora)
    {
        $flora->update(['is_featured' => !$flora->is_featured]);
        return response()->json([
            'success' => true,
            'message' => 'Status unggulan berhasil diperbarui.',
            'is_featured' => $flora->is_featured
        ]);
    }

    public function deleteImage(FloraImage $image)
    {
        $image->delete();
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Gambar berhasil dihapus']);
        }
        return back()->with('success', 'Gambar berhasil dihapus dari galeri.');
    }

    public function bulkDeleteMedia(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:flora_images,id',
        ]);

        $count = FloraImage::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} gambar berhasil dihapus.",
        ]);
    }

    public function duplicate(Flora $flora)
    {
        $newFlora = $this->floraService->duplicateFlora($flora);

        return response()->json([
            'success' => true,
            'message' => 'Flora berhasil diduplikasi.',
            'redirect' => route('admin.flora.edit', $newFlora),
        ]);
    }
}
