<?php

namespace App\Services\Admin;

use App\Models\Fauna;
use App\Models\FaunaImage;
use App\Services\ImageService;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;

class FaunaService
{
    protected ImageOptimizationService $optimizationService;

    public function __construct()
    {
        $this->optimizationService = new ImageOptimizationService();
    }

    /**
     * Create a new fauna item
     */
    public function createFauna(array $validated, Request $request): Fauna
    {
        $faunaData = [
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? \Illuminate\Support\Str::slug($validated['name']),
            'local_name' => $validated['local_name'] ?? null,
            'scientific_name' => $validated['scientific_name'] ?? null,
            'description' => $validated['description'] ?? null,
            'habitat' => $validated['habitat'] ?? null,
            'category' => $validated['category'] ?? 'umum',
            'conservation_status' => $validated['conservation_status'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => Fauna::max('sort_order') + 1,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
        ];

        // Handle cover image
        if ($request->hasFile('image')) {
            $imageData = ImageService::storeToDatabase($request->file('image'));
            $faunaData['image_data'] = $imageData['data'];
            $faunaData['image_mime'] = $imageData['mime'];
        } elseif ($request->filled('image_data')) {
            $imageData = $this->processBase64Image($request->image_data);
            if ($imageData) {
                $faunaData['image_data'] = $imageData;
                $faunaData['image_mime'] = 'image/jpeg';
            }
        }

        $fauna = Fauna::create($faunaData);
        $this->processGalleryImages($fauna, $request);

        return $fauna;
    }

    /**
     * Update an existing fauna item
     */
    public function updateFauna(Fauna $fauna, array $validated, Request $request): Fauna
    {
        $updateData = [
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? \Illuminate\Support\Str::slug($validated['name']),
            'local_name' => $validated['local_name'] ?? null,
            'scientific_name' => $validated['scientific_name'] ?? null,
            'description' => $validated['description'] ?? null,
            'habitat' => $validated['habitat'] ?? null,
            'category' => $validated['category'] ?? 'umum',
            'conservation_status' => $validated['conservation_status'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
        ];

        // Handle cover image update
        if ($request->hasFile('image')) {
            $imageData = ImageService::storeToDatabase($request->file('image'));
            $updateData['image_data'] = $imageData['data'];
            $updateData['image_mime'] = $imageData['mime'];
            // Clear old image_path if exists but don't set to null if column is not nullable
            if ($fauna->image_path) {
                $updateData['image_path'] = '';
            }
        } elseif ($request->filled('image_data')) {
            $imageData = $this->processBase64Image($request->image_data);
            if ($imageData) {
                $updateData['image_data'] = $imageData;
                $updateData['image_mime'] = 'image/jpeg';
                // Clear old image_path if exists but don't set to null if column is not nullable
                if ($fauna->image_path) {
                    $updateData['image_path'] = '';
                }
            }
        }

        $fauna->update($updateData);
        $this->processGalleryImages($fauna, $request);
        $this->processEditedImages($request);

        return $fauna;
    }

    /**
     * Process and store gallery images
     */
    protected function processGalleryImages(Fauna $fauna, Request $request): void
    {
        $sortOrder = $fauna->images()->max('sort_order') + 1;

        // Process uploaded files
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                $imageData = ImageService::storeToDatabase($image);
                FaunaImage::create([
                    'fauna_id' => $fauna->id,
                    'image_data' => $imageData['data'],
                    'image_mime' => $imageData['mime'],
                    'sort_order' => $sortOrder++,
                ]);
            }
        }

        // Process base64 images
        if ($request->filled('gallery_data')) {
            foreach ($request->gallery_data as $base64Image) {
                $imageData = $this->processBase64Image($base64Image);
                if ($imageData) {
                    FaunaImage::create([
                        'fauna_id' => $fauna->id,
                        'image_data' => $imageData,
                        'image_mime' => 'image/jpeg',
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }
        }
    }

    /**
     * Process edited gallery images (from Toast UI)
     */
    protected function processEditedImages(Request $request): void
    {
        if (!$request->filled('edited_gallery_data')) {
            return;
        }

        foreach ($request->edited_gallery_data as $imageId => $base64Image) {
            $image = FaunaImage::find($imageId);
            $imageData = $this->processBase64Image($base64Image);

            if ($image && $imageData) {
                $image->update([
                    'image_data' => $imageData,
                    'image_mime' => 'image/jpeg',
                ]);
            }
        }
    }

    /**
     * Process base64 image and return optimized base64 data
     */
    public function processBase64Image(string $base64Image): ?string
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64Image)) {
            return null;
        }

        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
        $imageData = base64_decode($imageData);
        $optimizedData = $this->optimizationService->optimizeFromData($imageData);

        return $optimizedData ? base64_encode($optimizedData) : null;
    }

    /**
     * Duplicate a fauna item with all its images
     */
    public function duplicateFauna(Fauna $fauna): Fauna
    {
        $newFauna = $fauna->replicate();
        $newFauna->name = $fauna->name . ' (Salinan)';
        $newFauna->slug = null;
        $newFauna->view_count = 0;
        $newFauna->is_active = true;
        $newFauna->sort_order = Fauna::max('sort_order') + 1;
        $newFauna->save();

        foreach ($fauna->images as $image) {
            $newImage = $image->replicate();
            $newImage->fauna_id = $newFauna->id;
            $newImage->save();
        }

        return $newFauna;
    }
}
