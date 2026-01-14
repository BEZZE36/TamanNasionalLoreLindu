<?php

namespace App\Services\Admin;

use App\Models\Destination;
use App\Models\DestinationImage;
use App\Models\DestinationPrice;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DestinationService
{
    /**
     * Create a new destination with images and prices
     */
    public function createDestination(array $validated, Request $request): Destination
    {
        $validated['slug'] = $this->generateUniqueSlug($validated['name']);
        $validated['facilities'] = $validated['facilities'] ?? [];
        $validated['rules'] = $validated['rules'] ?? [];
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        $destination = Destination::create($validated);

        $this->processCoverImage($destination, $request);
        $this->processGalleryImages($destination, $request);
        $this->processBase64Images($destination, $request);
        $this->processPrices($destination, $request);

        return $destination;
    }

    /**
     * Update a destination with images and prices
     */
    public function updateDestination(Destination $destination, array $validated, Request $request): Destination
    {
        if ($destination->name !== $validated['name']) {
            $validated['slug'] = $this->generateUniqueSlug($validated['name'], $destination->id);
        }

        $validated['facilities'] = $validated['facilities'] ?? [];
        $validated['rules'] = $validated['rules'] ?? [];
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        $destination->update($validated);

        if ($request->hasFile('cover_image')) {
            $destination->images()->delete();
            $this->processCoverImage($destination, $request);
        }

        $this->processGalleryImages($destination, $request);
        $this->processEditedImages($destination, $request);
        $this->processBase64Images($destination, $request);
        $this->updatePrices($destination, $request);

        return $destination;
    }

    /**
     * Process cover image
     */
    protected function processCoverImage(Destination $destination, Request $request): void
    {
        if (!$request->hasFile('cover_image'))
            return;

        $imageData = ImageService::storeToDatabase($request->file('cover_image'));
        $destination->images()->create([
            'image_data' => $imageData['data'],
            'image_mime' => $imageData['mime'],
            'is_primary' => true,
            'sort_order' => 0,
        ]);
    }

    /**
     * Process gallery images from file upload
     */
    protected function processGalleryImages(Destination $destination, Request $request): void
    {
        if (!$request->hasFile('gallery_images'))
            return;

        $lastSortOrder = $destination->images()->max('sort_order') ?? 0;

        foreach ($request->file('gallery_images') as $index => $image) {
            $imageData = ImageService::storeToDatabase($image);
            $destination->images()->create([
                'image_data' => $imageData['data'],
                'image_mime' => $imageData['mime'],
                'is_primary' => false,
                'sort_order' => $lastSortOrder + $index + 1,
            ]);
        }
    }

    /**
     * Process base64 images from editor
     */
    protected function processBase64Images(Destination $destination, Request $request): void
    {
        if (!$request->has('new_images_data'))
            return;

        $lastSortOrder = $destination->images()->max('sort_order') ?? 0;
        $index = 0;

        foreach ($request->new_images_data as $dataUrl) {
            if (!empty($dataUrl) && preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $matches)) {
                $mime = 'image/' . $matches[1];
                $base64Data = substr($dataUrl, strpos($dataUrl, ',') + 1);
                $imageData = base64_decode($base64Data);

                $destination->images()->create([
                    'image_data' => base64_encode($imageData),
                    'image_mime' => $mime,
                    'is_primary' => $lastSortOrder + $index === 0,
                    'sort_order' => $lastSortOrder + $index + 1,
                ]);
                $index++;
            }
        }
    }

    /**
     * Process edited images from image editor
     */
    protected function processEditedImages(Destination $destination, Request $request): void
    {
        if (!$request->has('edited_images_data'))
            return;

        foreach ($request->edited_images_data as $imageId => $dataUrl) {
            $existingImage = $destination->images()->find($imageId);
            if ($existingImage && !empty($dataUrl) && preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $matches)) {
                $mime = 'image/' . $matches[1];
                $base64Data = substr($dataUrl, strpos($dataUrl, ',') + 1);
                $imageData = base64_decode($base64Data);

                $existingImage->update([
                    'image_data' => base64_encode($imageData),
                    'image_mime' => $mime,
                ]);
            }
        }
    }

    /**
     * Process prices for new destination
     */
    protected function processPrices(Destination $destination, Request $request): void
    {
        if (!$request->filled('prices'))
            return;

        foreach ($request->prices as $priceData) {
            if (!empty($priceData['price']) && !empty($priceData['label'])) {
                $destination->prices()->create([
                    'category' => Str::slug($priceData['label']) . '-' . Str::random(5),
                    'label' => $priceData['label'],
                    'price' => $priceData['price'],
                    'description' => $priceData['description'] ?? null,
                    'is_active' => true,
                ]);
            }
        }
    }

    /**
     * Update prices for existing destination
     */
    protected function updatePrices(Destination $destination, Request $request): void
    {
        if (!$request->has('prices'))
            return;

        $currentPriceIds = [];

        foreach ($request->prices as $priceData) {
            // Support both 'label' and 'ticket_type' field names
            $label = $priceData['label'] ?? $priceData['ticket_type'] ?? '';
            $price = $priceData['price'] ?? 0;

            if (empty($label) || empty($price)) {
                continue;
            }

            $priceAttributes = [
                'label' => $label,
                'ticket_type' => $label,
                'price' => $price,
                'description' => $priceData['description'] ?? null,
                'is_active' => true,
            ];

            if (isset($priceData['id']) && $priceData['id']) {
                $existingPrice = $destination->prices()->find($priceData['id']);
                if ($existingPrice) {
                    $existingPrice->update($priceAttributes);
                    $currentPriceIds[] = $existingPrice->id;
                }
            } else {
                $priceAttributes['category'] = Str::slug($label) . '-' . Str::random(5);
                $newPrice = $destination->prices()->create($priceAttributes);
                $currentPriceIds[] = $newPrice->id;
            }
        }

        $destination->prices()->whereNotIn('id', $currentPriceIds)->delete();
    }

    /**
     * Duplicate a destination with images and prices
     */
    public function duplicateDestination(Destination $destination): Destination
    {
        $newDestination = $destination->replicate();
        $newDestination->name = $destination->name . ' (Salinan)';
        $newDestination->slug = $this->generateUniqueSlug($newDestination->name);
        $newDestination->is_active = true;
        $newDestination->created_at = now();
        $newDestination->updated_at = now();
        $newDestination->save();

        foreach ($destination->images as $image) {
            $newDestination->images()->create([
                'image_data' => $image->image_data,
                'image_mime' => $image->image_mime,
                'image_path' => $image->image_path,
                'is_primary' => $image->is_primary,
                'sort_order' => $image->sort_order,
            ]);
        }

        foreach ($destination->allPrices as $price) {
            $newDestination->prices()->create([
                'category' => $price->category,
                'label' => $price->label,
                'price' => $price->price,
                'description' => $price->description,
                'is_active' => $price->is_active,
            ]);
        }

        return $newDestination;
    }

    /**
     * Generate unique slug
     */
    public function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (
            Destination::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn($query, $ignoreId) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }
}
