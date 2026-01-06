<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flora;
use App\Models\FloraImage;
use App\Services\Admin\FloraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FloraController extends Controller
{
    protected FloraService $floraService;

    public function __construct(FloraService $floraService)
    {
        $this->floraService = $floraService;
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
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $this->floraService->createFlora($validated, $request);

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
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
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
