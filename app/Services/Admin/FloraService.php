<?php

namespace App\Services\Admin;

use App\Models\Flora;
use App\Models\FloraImage;
use App\Services\ImageService;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;

class FloraService
{
    protected ImageOptimizationService $optimizationService;

    public function __construct()
    {
        $this->optimizationService = new ImageOptimizationService();
    }

    /**
     * Create a new flora item
     */
    public function createFlora(array $validated, Request $request): Flora
    {
        $floraData = [
            'name' => $validated['name'],
            'scientific_name' => $validated['scientific_name'] ?? null,
            'description' => $validated['description'] ?? null,
            'habitat' => $validated['habitat'] ?? null,
            'conservation_status' => $validated['conservation_status'] ?? null,
            'is_endemic' => $request->boolean('is_endemic'),
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => Flora::max('sort_order') + 1,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
        ];

        // Handle cover image
        if ($request->hasFile('image')) {
            $imageData = ImageService::storeToDatabase($request->file('image'));
            $floraData['image_data'] = $imageData['data'];
            $floraData['image_mime'] = $imageData['mime'];
        } elseif ($request->filled('image_data')) {
            $imageData = $this->processBase64Image($request->image_data);
            if ($imageData) {
                $floraData['image_data'] = $imageData;
                $floraData['image_mime'] = 'image/jpeg';
            }
        }

        $flora = Flora::create($floraData);
        $this->processGalleryImages($flora, $request);

        return $flora;
    }

    /**
     * Update an existing flora item
     */
    public function updateFlora(Flora $flora, array $validated, Request $request): Flora
    {
        $updateData = [
            'name' => $validated['name'],
            'scientific_name' => $validated['scientific_name'] ?? null,
            'description' => $validated['description'] ?? null,
            'habitat' => $validated['habitat'] ?? null,
            'conservation_status' => $validated['conservation_status'] ?? null,
            'is_endemic' => $request->boolean('is_endemic'),
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
            $updateData['image_path'] = null;
        } elseif ($request->filled('image_data')) {
            $imageData = $this->processBase64Image($request->image_data);
            if ($imageData) {
                $updateData['image_data'] = $imageData;
                $updateData['image_mime'] = 'image/jpeg';
                $updateData['image_path'] = null;
            }
        }

        $flora->update($updateData);
        $this->processGalleryImages($flora, $request);
        $this->processEditedImages($request);

        return $flora;
    }

    /**
     * Process and store gallery images
     */
    protected function processGalleryImages(Flora $flora, Request $request): void
    {
        $sortOrder = $flora->images()->max('sort_order') + 1;

        // Process uploaded files
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                $imageData = ImageService::storeToDatabase($image);
                FloraImage::create([
                    'flora_id' => $flora->id,
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
                    FloraImage::create([
                        'flora_id' => $flora->id,
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
            $image = FloraImage::find($imageId);
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
     * Duplicate a flora item with all its images
     */
    public function duplicateFlora(Flora $flora): Flora
    {
        $newFlora = $flora->replicate();
        $newFlora->name = $flora->name . ' (Salinan)';
        $newFlora->slug = null;
        $newFlora->view_count = 0;
        $newFlora->sort_order = Flora::max('sort_order') + 1;
        $newFlora->save();

        foreach ($flora->images as $image) {
            $newImage = $image->replicate();
            $newImage->flora_id = $newFlora->id;
            $newImage->save();
        }

        return $newFlora;
    }
}
