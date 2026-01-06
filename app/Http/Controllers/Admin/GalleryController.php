<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\GalleryMedia;
use App\Models\Destination;
use App\Services\Admin\GalleryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GalleryController extends Controller
{
    protected GalleryService $galleryService;

    public function __construct(GalleryService $galleryService)
    {
        $this->galleryService = $galleryService;
    }

    public function index(Request $request)
    {
        $query = Gallery::with(['destination', 'media']);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('destination')) {
            $query->where('destination_id', $request->destination);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $galleries = $query->ordered()->paginate(15)->withQueryString();
        $galleries->getCollection()->transform(fn($g) => [
            'id' => $g->id,
            'title' => $g->title,
            'location' => $g->location,
            'cover_url' => $g->cover_url,
            'is_active' => $g->is_active,
            'is_featured' => $g->is_featured,
            'has_video' => $g->media->where('type', 'video')->count() > 0,
            'media_count' => $g->media->count(),
            'destination' => $g->destination ? ['id' => $g->destination->id, 'name' => $g->destination->name] : null,
        ]);
        $destinations = Destination::active()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Gallery/Index', [
            'galleries' => $galleries,
            'destinations' => $destinations,
            'filters' => $request->only(['search', 'destination', 'status']),
        ]);
    }

    public function create()
    {
        $destinations = Destination::active()->orderBy('name')->get(['id', 'name']);
        $categories = \App\Models\GalleryCategory::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Gallery/Create', [
            'destinations' => $destinations,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'gallery_category_id' => 'nullable|exists:gallery_categories,id',
            'location' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'video_urls' => 'nullable|array',
            'video_urls.*' => 'nullable|url',
            'destination_id' => 'nullable|exists:destinations,id',
            'tags' => 'nullable|string',
            'capture_date' => 'nullable|date',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:170',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $this->galleryService->createGallery($validated, $request);

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Album galeri berhasil ditambahkan.');
    }

    public function edit(Gallery $gallery)
    {
        $gallery->load('media');
        $destinations = Destination::active()->orderBy('name')->get(['id', 'name']);
        $categories = \App\Models\GalleryCategory::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        $galleryData = [
            'id' => $gallery->id,
            'title' => $gallery->title,
            'gallery_category_id' => $gallery->gallery_category_id,
            'location' => $gallery->location,
            'description' => $gallery->description,
            'capture_date' => optional($gallery->capture_date)->format('Y-m-d'),
            'destination_id' => $gallery->destination_id,
            'tags' => $gallery->tags ?? [],
            'is_active' => $gallery->is_active,
            'is_featured' => $gallery->is_featured,
            'cover_url' => $gallery->cover_url,
            'video_urls' => $gallery->video_urls ?? [],
            'meta_title' => $gallery->meta_title,
            'meta_description' => $gallery->meta_description,
            'meta_keywords' => $gallery->meta_keywords,
            'media' => $gallery->media->map(fn($m) => [
                'id' => $m->id,
                'type' => $m->type,
                'file_url' => $m->file_url,
                'thumbnail_url' => $m->thumbnail_url ?? $m->file_url,
            ])->toArray(),
        ];

        return Inertia::render('Admin/Gallery/Edit', [
            'gallery' => $galleryData,
            'destinations' => $destinations,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Gallery $gallery)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'gallery_category_id' => 'nullable|exists:gallery_categories,id',
            'location' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'video_urls' => 'nullable|array',
            'video_urls.*' => 'nullable|url',
            'destination_id' => 'nullable|exists:destinations,id',
            'tags' => 'nullable|string',
            'capture_date' => 'nullable|date',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:170',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $this->galleryService->updateGallery($gallery, $validated, $request);

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Album galeri berhasil diperbarui.');
    }

    public function destroyMedia(GalleryMedia $media)
    {
        $media->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Media berhasil dihapus']);
        }

        return back()->with('success', 'Media berhasil dihapus dari album.');
    }

    public function bulkDeleteMedia(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:gallery_media,id',
        ]);

        $count = GalleryMedia::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} media berhasil dihapus.",
        ]);
    }

    public function destroy(Gallery $gallery)
    {
        $gallery->media()->delete();
        $gallery->delete();

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Album galeri berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:galleries,id',
        ]);

        $count = 0;
        foreach ($request->ids as $id) {
            $gallery = Gallery::find($id);
            if ($gallery) {
                $gallery->media()->delete();
                $gallery->delete();
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$count} album berhasil dihapus.",
        ]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:galleries,id',
        ]);

        foreach ($request->order as $index => $id) {
            Gallery::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Urutan album berhasil diperbarui.',
        ]);
    }

    public function cropMedia(Request $request, GalleryMedia $media)
    {
        $request->validate(['image_data' => 'required|string']);

        $success = $this->galleryService->cropMedia($media, $request->image_data);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Gambar berhasil di-crop dan disimpan.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Format gambar tidak valid.',
        ], 400);
    }

    public function toggleActive(Gallery $gallery)
    {
        $gallery->update(['is_active' => !$gallery->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $gallery->is_active,
            'message' => $gallery->is_active ? 'Album diaktifkan.' : 'Album dinonaktifkan.',
        ]);
    }

    public function toggleFeatured(Gallery $gallery)
    {
        $gallery->update(['is_featured' => !$gallery->is_featured]);

        return response()->json([
            'success' => true,
            'is_featured' => $gallery->is_featured,
            'message' => $gallery->is_featured ? 'Album dijadikan unggulan.' : 'Album dihapus dari unggulan.',
        ]);
    }

    public function duplicate(Gallery $gallery)
    {
        $newGallery = $this->galleryService->duplicateGallery($gallery);

        return response()->json([
            'success' => true,
            'message' => 'Album berhasil diduplikasi.',
            'redirect' => route('admin.gallery.edit', $newGallery),
        ]);
    }

    public function preview(Gallery $gallery)
    {
        $gallery->load(['destination', 'category', 'media']);

        $galleryData = [
            'id' => $gallery->id,
            'title' => $gallery->title,
            'slug' => $gallery->slug,
            'location' => $gallery->location,
            'description' => $gallery->description,
            'is_active' => $gallery->is_active,
            'is_featured' => $gallery->is_featured,
            'view_count' => $gallery->view_count ?? 0,
            'image_url' => $gallery->cover_url,
            'category' => $gallery->category ? ['id' => $gallery->category->id, 'name' => $gallery->category->name] : null,
            'destination' => $gallery->destination ? ['id' => $gallery->destination->id, 'name' => $gallery->destination->name] : null,
            'tags' => $gallery->tags ?? [],
            'meta_title' => $gallery->meta_title,
            'meta_description' => $gallery->meta_description,
            'meta_keywords' => $gallery->meta_keywords,
            'created_at' => $gallery->created_at,
            'updated_at' => $gallery->updated_at,
            'media' => $gallery->media->map(fn($m) => [
                'id' => $m->id,
                'type' => $m->type,
                'image_url' => $m->file_url,
                'alt_text' => $m->alt_text ?? $gallery->title,
            ])->toArray(),
        ];

        return Inertia::render('Admin/Gallery/Preview', ['gallery' => $galleryData]);
    }
}
