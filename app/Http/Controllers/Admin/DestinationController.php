<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\DestinationImage;
use App\Models\DestinationCategory;
use App\Services\Admin\DestinationService;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    protected DestinationService $destinationService;

    public function __construct(DestinationService $destinationService)
    {
        $this->destinationService = $destinationService;
    }

    public function index(Request $request)
    {
        $query = Destination::with(['images', 'prices', 'category']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $destinations = $query->latest()->paginate(10)->withQueryString();

        $filters = [
            'search' => $request->search,
            'status' => $request->status,
        ];

        return \Inertia\Inertia::render('Admin/Destinations/Index', compact('destinations', 'filters'));
    }

    public function show(Destination $destination)
    {
        $destination->load(['images', 'prices', 'category', 'bookings']);
        return \Inertia\Inertia::render('Admin/Destinations/Show', compact('destination'));
    }

    public function create()
    {
        $categories = DestinationCategory::all();
        return \Inertia\Inertia::render('Admin/Destinations/Create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());

        $this->destinationService->createDestination($validated, $request);

        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destinasi berhasil ditambahkan.');
    }

    public function edit(Destination $destination)
    {
        $destination->load(['images', 'prices']);
        $categories = DestinationCategory::all();
        return \Inertia\Inertia::render('Admin/Destinations/Edit', compact('destination', 'categories'));
    }

    public function update(Request $request, Destination $destination)
    {
        $validated = $request->validate($this->validationRules());

        $this->destinationService->updateDestination($destination, $validated, $request);

        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destinasi berhasil diperbarui.');
    }

    public function destroy(Destination $destination)
    {
        $destination->delete();

        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destinasi berhasil dihapus.');
    }

    public function deleteImage(DestinationImage $image)
    {
        $image->delete();
        return back()->with('success', 'Gambar berhasil dihapus.');
    }

    public function setPrimaryImage(DestinationImage $image)
    {
        $image->destination->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return back()->with('success', 'Gambar utama berhasil diubah.');
    }

    public function toggleActive(Destination $destination)
    {
        $destination->update(['is_active' => !$destination->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $destination->is_active,
            'message' => $destination->is_active ? 'Destinasi diaktifkan' : 'Destinasi dinonaktifkan'
        ]);
    }

    public function toggleFeatured(Destination $destination)
    {
        $destination->update(['is_featured' => !$destination->is_featured]);

        return response()->json([
            'success' => true,
            'is_featured' => $destination->is_featured,
            'message' => $destination->is_featured ? 'Destinasi dijadikan unggulan' : 'Destinasi bukan unggulan'
        ]);
    }

    public function duplicate(Destination $destination)
    {
        $newDestination = $this->destinationService->duplicateDestination($destination);

        return response()->json([
            'success' => true,
            'message' => 'Destinasi berhasil diduplikasi',
            'redirect' => route('admin.destinations.edit', $newDestination)
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:destinations,id'
        ]);

        $count = count($request->ids);
        Destination::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => $count . ' destinasi berhasil dihapus'
        ]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:destinations,id'
        ]);

        foreach ($request->order as $index => $id) {
            Destination::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Urutan berhasil diperbarui'
        ]);
    }

    /**
     * Common validation rules
     */
    protected function validationRules(): array
    {
        return [
            'category_id' => 'nullable|exists:destination_categories,id',
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'operating_hours' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'google_maps_embed' => 'nullable|string',
            'facilities' => 'nullable|array',
            'rules' => 'nullable|array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'rating' => 'nullable|numeric|min:0|max:5',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'prices' => 'nullable|array',
            'parking_fees' => 'nullable|array',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'keywords' => 'nullable|string',
        ];
    }
}
