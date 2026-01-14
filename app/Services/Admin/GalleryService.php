<?php

namespace App\Services\Admin;

use App\Models\Gallery;
use App\Models\GalleryMedia;
use App\Services\ImageService;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class GalleryService
{
    protected ImageOptimizationService $optimizationService;

    public function __construct()
    {
        $this->optimizationService = new ImageOptimizationService();
    }

    /**
     * Create a new gallery album
     */
    public function createGallery(array $validated, Request $request): Gallery
    {
        $coverData = ImageService::storeToDatabase($request->file('cover_image'));

        $gallery = Gallery::create([
            'title' => $validated['title'],
            'gallery_category_id' => $validated['gallery_category_id'] ?? null,
            'location' => $validated['location'] ?? null,
            'description' => $validated['description'] ?? null,
            'destination_id' => $validated['destination_id'] ?? null,
            'tags' => $request->filled('tags') ? json_decode($request->tags, true) : null,
            'capture_date' => $validated['capture_date'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
            'image_data' => $coverData['data'],
            'image_mime' => $coverData['mime'],
            'sort_order' => Gallery::max('sort_order') + 1,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'meta_keywords' => $validated['meta_keywords'] ?? null,
        ]);

        $this->processGalleryMedia($gallery, $validated, $request);

        return $gallery;
    }

    /**
     * Update an existing gallery album
     */
    public function updateGallery(Gallery $gallery, array $validated, Request $request): Gallery
    {
        $updateData = [
            'title' => $validated['title'],
            'gallery_category_id' => $validated['gallery_category_id'] ?? null,
            'location' => $validated['location'] ?? null,
            'description' => $validated['description'] ?? null,
            'destination_id' => $validated['destination_id'] ?? null,
            'tags' => $request->filled('tags') ? json_decode($request->tags, true) : null,
            'capture_date' => $validated['capture_date'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'meta_keywords' => $validated['meta_keywords'] ?? null,
        ];

        // Update cover image if file uploaded
        if ($request->hasFile('cover_image')) {
            $coverData = ImageService::storeToDatabase($request->file('cover_image'));
            $updateData['image_data'] = $coverData['data'];
            $updateData['image_mime'] = $coverData['mime'];
            $updateData['image_path'] = null;
        }

        // Handle cover image edited via Toast UI (base64)
        if ($request->filled('cover_image_data')) {
            $coverData = $this->processBase64Image($request->cover_image_data);
            if ($coverData) {
                $updateData['image_data'] = $coverData;
                $updateData['image_mime'] = 'image/jpeg';
                $updateData['image_path'] = null;
            }
        }

        $gallery->update($updateData);
        $this->processGalleryMedia($gallery, $validated, $request);
        $this->processEditedMedia($request);

        return $gallery;
    }

    /**
     * Process and store gallery media (images/videos)
     */
    protected function processGalleryMedia(Gallery $gallery, array $validated, Request $request): void
    {
        $sortOrder = $gallery->media()->max('sort_order') + 1;

        // Process uploaded files
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $this->storeImageMedia($gallery, $image, $validated['title'], $sortOrder++);
            }
        }

        // Process base64 images from Dropzone
        if ($request->filled('images_data')) {
            foreach ($request->images_data as $base64Image) {
                $imageData = $this->processBase64Image($base64Image);
                if ($imageData) {
                    GalleryMedia::create([
                        'gallery_id' => $gallery->id,
                        'type' => 'image',
                        'image_data' => $imageData,
                        'image_mime' => 'image/jpeg',
                        'alt_text' => $validated['title'],
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }
        }

        // Process video URLs
        if ($request->filled('video_urls')) {
            foreach ($request->video_urls as $videoUrl) {
                if (!empty($videoUrl)) {
                    GalleryMedia::create([
                        'gallery_id' => $gallery->id,
                        'type' => 'video',
                        'video_url' => $videoUrl,
                        'alt_text' => $validated['title'] . ' - Video',
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }
        }
    }

    /**
     * Store an uploaded image as gallery media
     */
    protected function storeImageMedia(Gallery $gallery, UploadedFile $image, string $altText, int $sortOrder): void
    {
        $imageData = ImageService::storeToDatabase($image);
        GalleryMedia::create([
            'gallery_id' => $gallery->id,
            'type' => 'image',
            'image_data' => $imageData['data'],
            'image_mime' => $imageData['mime'],
            'alt_text' => $altText,
            'sort_order' => $sortOrder,
        ]);
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
     * Process edited media items (from Toast UI Image Editor)
     */
    protected function processEditedMedia(Request $request): void
    {
        if (!$request->filled('edited_media_data')) {
            return;
        }

        foreach ($request->edited_media_data as $mediaId => $base64Image) {
            $media = GalleryMedia::find($mediaId);
            $imageData = $this->processBase64Image($base64Image);

            if ($media && $imageData) {
                $media->update([
                    'image_data' => $imageData,
                    'image_mime' => 'image/jpeg',
                ]);
            }
        }
    }

    /**
     * Duplicate a gallery album with all its media
     */
    public function duplicateGallery(Gallery $gallery): Gallery
    {
        $newGallery = $gallery->replicate();
        $newGallery->title = $gallery->title . ' (Salinan)';
        $newGallery->slug = null;
        $newGallery->view_count = 0;
        $newGallery->is_active = true;
        $newGallery->sort_order = Gallery::max('sort_order') + 1;
        $newGallery->created_at = now();
        $newGallery->updated_at = now();
        $newGallery->save();

        foreach ($gallery->media as $media) {
            $newMedia = $media->replicate();
            $newMedia->gallery_id = $newGallery->id;
            $newMedia->save();
        }

        return $newGallery;
    }

    /**
     * Crop media image and update
     */
    public function cropMedia(GalleryMedia $media, string $base64Image): bool
    {
        $imageData = $this->processBase64Image($base64Image);

        if (!$imageData) {
            return false;
        }

        $media->update([
            'image_data' => $imageData,
            'image_mime' => 'image/jpeg',
        ]);

        return true;
    }
}
