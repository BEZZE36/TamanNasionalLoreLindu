<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fauna;
use App\Models\FaunaImage;
use App\Services\Admin\FaunaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaunaController extends Controller
{
    protected FaunaService $faunaService;

    public function __construct(FaunaService $faunaService)
    {
        $this->faunaService = $faunaService;
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
            'conservation_status' => 'required|in:LC,NT,VU,EN,CR',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'category' => 'required|in:umum,langka,endemik',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $this->faunaService->createFauna($validated, $request);

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
            'conservation_status' => 'required|in:LC,NT,VU,EN,CR',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'category' => 'required|in:umum,langka,endemik',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
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
