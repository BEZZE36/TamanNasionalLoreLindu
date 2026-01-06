<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GalleryCategoryController extends Controller
{
    public function index()
    {
        $categories = GalleryCategory::withCount('galleries')->orderBy('name')->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'description' => $c->description,
                'is_active' => $c->is_active,
                'galleries_count' => $c->galleries_count,
            ]);
        return Inertia::render('Admin/GalleryCategories/Index', ['categories' => $categories]);
    }

    public function create()
    {
        return Inertia::render('Admin/GalleryCategories/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:gallery_categories,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        GalleryCategory::create($validated);

        return redirect()->route('admin.gallery-categories.index')
            ->with('success', 'Kategori galeri berhasil ditambahkan.');
    }

    public function edit(GalleryCategory $galleryCategory)
    {
        return Inertia::render('Admin/GalleryCategories/Edit', [
            'galleryCategory' => [
                'id' => $galleryCategory->id,
                'name' => $galleryCategory->name,
                'description' => $galleryCategory->description,
                'is_active' => $galleryCategory->is_active,
            ]
        ]);
    }

    public function update(Request $request, GalleryCategory $galleryCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:gallery_categories,name,' . $galleryCategory->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $galleryCategory->update($validated);

        return redirect()->route('admin.gallery-categories.index')
            ->with('success', 'Kategori galeri berhasil diperbarui.');
    }

    public function destroy(GalleryCategory $galleryCategory)
    {
        if ($galleryCategory->galleries()->count() > 0) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki item galeri.');
        }

        $galleryCategory->delete();

        return redirect()->route('admin.gallery-categories.index')
            ->with('success', 'Kategori galeri berhasil dihapus.');
    }
}
