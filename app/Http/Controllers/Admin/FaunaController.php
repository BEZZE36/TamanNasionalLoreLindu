<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fauna;
use App\Models\FaunaImage;
use App\Services\Admin\FaunaService;
use App\Services\NotificationService;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaunaController extends Controller
{
    protected FaunaService $faunaService;
    protected NotificationService $notificationService;
    protected PushNotificationService $pushNotificationService;

    public function __construct(
        FaunaService $faunaService,
        NotificationService $notificationService,
        PushNotificationService $pushNotificationService
    ) {
        $this->faunaService = $faunaService;
        $this->notificationService = $notificationService;
        $this->pushNotificationService = $pushNotificationService;
    }

    public function dashboard(Request $request)
    {
        $period = $request->get('period', 30);
        $startDate = now()->subDays($period);

        $stats = [
            'total' => Fauna::count(),
            'published' => Fauna::where('is_active', true)->count(),
            'draft' => Fauna::where('is_active', false)->count(),
            'featured' => Fauna::where('is_featured', true)->count(),
            'endangered' => Fauna::where('category', 'langka')->count(),
            'endemic' => Fauna::where('category', 'endemik')->count(),
            'total_views' => Fauna::sum('view_count'),
            'total_comments' => \App\Models\FaunaComment::count(),
        ];

        // Generate viewsChart data based on created_at dates
        $viewsChart = collect(range(0, $period - 1))->map(function ($i) use ($startDate) {
            $date = $startDate->copy()->addDays($i);
            return [
                'date' => $date->format('d M'),
                'views' => Fauna::whereDate('created_at', $date)->sum('view_count'),
            ];
        })->values()->toArray();

        $topFauna = Fauna::orderBy('view_count', 'desc')
            ->take(10)
            ->get()
            ->map(fn($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'category' => $f->category,
                'views_count' => $f->view_count,
            ]);

        $recentFauna = Fauna::latest()
            ->take(8)
            ->get()
            ->map(fn($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'scientific_name' => $f->scientific_name,
                'category' => $f->category,
                'image_url' => $f->image_url,
            ]);

        return \Inertia\Inertia::render('Admin/Fauna/Dashboard', [
            'stats' => $stats,
            'viewsChart' => $viewsChart,
            'topFauna' => $topFauna,
            'recentFauna' => $recentFauna,
            'period' => $period,
        ]);
    }

    public function index(Request $request)
    {
        $query = Fauna::query();

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

        if ($request->filled('conservation')) {
            $query->where('conservation_status', $request->conservation);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $fauna = $query->ordered()->paginate(15)->withQueryString();

        $totalFauna = Fauna::count();
        $activeFauna = Fauna::where('is_active', true)->count();
        $featuredFauna = Fauna::where('is_featured', true)->count();
        $inactiveFauna = Fauna::where('is_active', false)->count();

        $filters = ['search' => request()->search, 'category' => request()->category, 'status' => request()->status];
        $categories = Fauna::CATEGORIES;

        return \Inertia\Inertia::render('Admin/Fauna/Index', compact('fauna', 'totalFauna', 'activeFauna', 'featuredFauna', 'inactiveFauna', 'filters', 'categories'));
    }

    public function create()
    {
        $categories = Fauna::CATEGORIES;
        $conservationStatuses = Fauna::CONSERVATION_STATUSES;
        return \Inertia\Inertia::render('Admin/Fauna/Create', compact('categories', 'conservationStatuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:fauna,slug',
            'local_name' => 'nullable|string|max:255',
            'scientific_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'habitat' => 'nullable|string|max:255',
            'conservation_status' => 'nullable|in:LC,NT,VU,EN,CR',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'category' => 'required|in:umum,langka,endemik',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'gallery' => 'nullable|array',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $fauna = $this->faunaService->createFauna($validated, $request);

        // Send notifications to all users
        if ($fauna->is_active) {
            $url = route('fauna.show', $fauna->slug);
            $this->notificationService->notifyNewFauna(
                $fauna->name,
                $fauna->description ?? '',
                $url
            );
            $this->pushNotificationService->notifyNewFauna(
                $fauna->name,
                $fauna->description ?? '',
                $url
            );
        }

        return redirect()->route('admin.fauna.index')
            ->with('success', 'Fauna berhasil ditambahkan.');
    }

    public function edit(Fauna $fauna)
    {
        $fauna->load('images');
        $categories = Fauna::CATEGORIES;
        $conservationStatuses = Fauna::CONSERVATION_STATUSES;
        return \Inertia\Inertia::render('Admin/Fauna/Edit', compact('fauna', 'categories', 'conservationStatuses'));
    }

    public function update(Request $request, Fauna $fauna)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:fauna,slug,' . $fauna->id,
            'local_name' => 'nullable|string|max:255',
            'scientific_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'habitat' => 'nullable|string|max:255',
            'conservation_status' => 'nullable|in:LC,NT,VU,EN,CR',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'category' => 'required|in:umum,langka,endemik',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'gallery' => 'nullable|array',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $this->faunaService->updateFauna($fauna, $validated, $request);

        return redirect()->route('admin.fauna.index')
            ->with('success', 'Fauna berhasil diperbarui.');
    }

    public function destroy(Fauna $fauna)
    {
        if ($fauna->image_path) {
            Storage::disk('public')->delete($fauna->image_path);
        }
        $fauna->images()->delete();
        $fauna->delete();

        return redirect()->route('admin.fauna.index')
            ->with('success', 'Fauna berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:fauna,id',
        ]);

        $count = 0;
        foreach ($request->ids as $id) {
            $fauna = Fauna::find($id);
            if ($fauna) {
                if ($fauna->image_path) {
                    Storage::disk('public')->delete($fauna->image_path);
                }
                $fauna->images()->delete();
                $fauna->delete();
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$count} fauna berhasil dihapus.",
        ]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:fauna,id',
        ]);

        foreach ($request->order as $index => $id) {
            Fauna::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function toggleActive(Fauna $fauna)
    {
        $fauna->update(['is_active' => !$fauna->is_active]);
        return response()->json([
            'success' => true,
            'message' => 'Status fauna berhasil diperbarui.',
            'is_active' => $fauna->is_active
        ]);
    }

    public function toggleFeatured(Fauna $fauna)
    {
        $fauna->update(['is_featured' => !$fauna->is_featured]);
        return response()->json([
            'success' => true,
            'message' => 'Status unggulan berhasil diperbarui.',
            'is_featured' => $fauna->is_featured
        ]);
    }

    public function deleteImage(FaunaImage $image)
    {
        $image->delete();
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Gambar berhasil dihapus']);
        }
        return back()->with('success', 'Gambar berhasil dihapus dari galeri.');
    }

    public function deleteCoverImage($id)
    {
        $fauna = Fauna::findOrFail($id);
        if ($fauna->image_path) {
            Storage::disk('public')->delete($fauna->image_path);
        }
        $fauna->update([
            'image_path' => '',
            'image_data' => null,
            'image_mime' => null
        ]);
        return back()->with('success', 'Gambar sampul berhasil dihapus.');
    }

    public function bulkDeleteMedia(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:fauna_images,id',
        ]);

        $count = FaunaImage::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} gambar berhasil dihapus.",
        ]);
    }

    public function duplicate(Fauna $fauna)
    {
        $newFauna = $this->faunaService->duplicateFauna($fauna);

        return response()->json([
            'success' => true,
            'message' => 'Fauna berhasil diduplikasi.',
            'redirect' => route('admin.fauna.edit', $newFauna),
        ]);
    }
}
