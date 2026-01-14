<?php

namespace App\Models\Traits;

trait HasGalleryMedia
{
    /**
     * Get the image URL from database or storage.
     */
    public function getImageUrlAttribute(): ?string
    {
        // Prioritize database storage
        if ($this->image_data) {
            return route('images.gallery', $this->id);
        }

        if ($this->image_path) {
            if (str_starts_with($this->image_path, 'http')) {
                return $this->image_path;
            }
            // Check if file exists in storage first
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->image_path)) {
                return asset('storage/' . $this->image_path);
            }
            // Fallback to public/assets folder
            return asset('assets/' . $this->image_path);
        }

        return null;
    }

    /**
     * Get media URL (image or video).
     */
    public function getMediaUrlAttribute(): ?string
    {
        if ($this->type === self::TYPE_VIDEO) {
            return $this->video_url;
        }

        return $this->image_url;
    }

    /**
     * Get thumbnail URL (for videos, extract from YouTube).
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->type === self::TYPE_VIDEO && $this->video_url) {
            preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\\w-]+)/', $this->video_url, $matches);
            if (!empty($matches[1])) {
                return "https://img.youtube.com/vi/{$matches[1]}/mqdefault.jpg";
            }
        }

        return $this->media_url;
    }
}
