<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order')->get()->map(fn($b) => [
            'id' => $b->id,
            'title' => $b->title,
            'subtitle' => $b->subtitle,
            'image_url' => $b->image_url,
            'link_url' => $b->link_url,
            'is_active' => $b->is_active,
            'start_at' => $b->start_at,
            'end_at' => $b->end_at,
        ]);
        return Inertia::render('Admin/Banners/Index', ['banners' => $banners]);
    }

    public function create()
    {
        return Inertia::render('Admin/Banners/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'link_url' => 'nullable|string|max:500',
            'link_target' => 'nullable|in:_self,_blank',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after:start_at',
            'is_active' => 'boolean',
        ]);

        // Handle desktop image upload - store in database
        if ($request->hasFile('image')) {
            $imageData = ImageService::storeToDatabase($request->file('image'));
            $validated['image_data'] = $imageData['data'];
            $validated['image_mime'] = $imageData['mime'];
        }

        // Handle mobile image upload - store in database
        if ($request->hasFile('mobile_image')) {
            $mobileData = ImageService::storeToDatabase($request->file('mobile_image'));
            $validated['mobile_image_data'] = $mobileData['data'];
            $validated['mobile_image_mime'] = $mobileData['mime'];
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = Banner::max('sort_order') + 1;
        $validated['link_target'] = $validated['link_target'] ?? '_self';

        Banner::create($validated);

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner berhasil ditambahkan.');
    }

    public function edit(Banner $banner)
    {
        return Inertia::render('Admin/Banners/Edit', [
            'banner' => [
                'id' => $banner->id,
                'title' => $banner->title,
                'subtitle' => $banner->subtitle,
                'image_url' => $banner->image_url,
                'mobile_image_url' => $banner->mobile_image_url,
                'link_url' => $banner->link_url,
                'link_target' => $banner->link_target,
                'start_at' => $banner->start_at?->toISOString(),
                'end_at' => $banner->end_at?->toISOString(),
                'is_active' => $banner->is_active,
            ]
        ]);
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'link_url' => 'nullable|string|max:500',
            'link_target' => 'nullable|in:_self,_blank',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after:start_at',
            'is_active' => 'boolean',
        ]);

        // Handle desktop image upload - store in database
        if ($request->hasFile('image')) {
            $imageData = ImageService::storeToDatabase($request->file('image'));
            $validated['image_data'] = $imageData['data'];
            $validated['image_mime'] = $imageData['mime'];
            $validated['image_path'] = null;
            if ($banner->image_path) {
                Storage::disk('public')->delete($banner->image_path);
            }
        }

        // Handle mobile image upload - store in database
        if ($request->hasFile('mobile_image')) {
            $mobileData = ImageService::storeToDatabase($request->file('mobile_image'));
            $validated['mobile_image_data'] = $mobileData['data'];
            $validated['mobile_image_mime'] = $mobileData['mime'];
            $validated['mobile_image_path'] = null;
            if ($banner->mobile_image_path) {
                Storage::disk('public')->delete($banner->mobile_image_path);
            }
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['link_target'] = $validated['link_target'] ?? '_self';

        $banner->update($validated);

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner berhasil diperbarui.');
    }

    public function destroy(Banner $banner)
    {
        // Delete images
        Storage::disk('public')->delete($banner->image_path);
        if ($banner->mobile_image_path) {
            Storage::disk('public')->delete($banner->mobile_image_path);
        }

        $banner->delete();

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner berhasil dihapus.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:banners,id',
        ]);

        foreach ($request->order as $index => $id) {
            Banner::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
