<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DestinationCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DestinationCategoryController extends Controller
{
    public function index()
    {
        $categories = DestinationCategory::withCount('destinations')->latest()->paginate(10)
            ->through(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'description' => $c->description,
                'icon' => $c->icon,
                'destinations_count' => $c->destinations_count,
            ]);
        return Inertia::render('Admin/DestinationCategories/Index', ['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        DestinationCategory::create($validated);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, DestinationCategory $destinationCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
        ]);

        if ($destinationCategory->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $destinationCategory->update($validated);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(DestinationCategory $destinationCategory)
    {
        if ($destinationCategory->destinations()->count() > 0) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh destinasi lain.');
        }

        $destinationCategory->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
