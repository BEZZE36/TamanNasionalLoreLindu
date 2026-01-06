<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ImageOptimizationService
{
    protected int $maxWidth = 1920;
    protected int $maxHeight = 1080;
    protected int $quality = 80;
    protected int $thumbnailWidth = 400;
    protected int $thumbnailHeight = 300;

    /**
     * Optimize image from uploaded file
     */
    public function optimize(UploadedFile $file): ?string
    {
        try {
            $imageData = file_get_contents($file->getRealPath());
            $image = imagecreatefromstring($imageData);

            if (!$image) {
                return $imageData;
            }

            // Get original dimensions
            $width = imagesx($image);
            $height = imagesy($image);

            // Calculate new dimensions
            $ratio = min($this->maxWidth / $width, $this->maxHeight / $height);

            if ($ratio < 1) {
                $newWidth = (int) ($width * $ratio);
                $newHeight = (int) ($height * $ratio);

                $optimized = imagecreatetruecolor($newWidth, $newHeight);

                // Preserve transparency for PNG
                imagealphablending($optimized, false);
                imagesavealpha($optimized, true);

                imagecopyresampled($optimized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                imagedestroy($image);
                $image = $optimized;
            }

            // Output as JPEG with compression
            ob_start();
            imagejpeg($image, null, $this->quality);
            $result = ob_get_clean();

            imagedestroy($image);

            return $result;
        } catch (\Exception $e) {
            Log::error('Image optimization failed: ' . $e->getMessage());
            return file_get_contents($file->getRealPath());
        }
    }

    /**
     * Optimize image from binary data
     */
    public function optimizeFromData(string $imageData): string
    {
        try {
            $image = imagecreatefromstring($imageData);

            if (!$image) {
                return $imageData;
            }

            $width = imagesx($image);
            $height = imagesy($image);

            $ratio = min($this->maxWidth / $width, $this->maxHeight / $height);

            if ($ratio < 1) {
                $newWidth = (int) ($width * $ratio);
                $newHeight = (int) ($height * $ratio);

                $optimized = imagecreatetruecolor($newWidth, $newHeight);
                imagealphablending($optimized, false);
                imagesavealpha($optimized, true);
                imagecopyresampled($optimized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                imagedestroy($image);
                $image = $optimized;
            }

            ob_start();
            imagejpeg($image, null, $this->quality);
            $result = ob_get_clean();

            imagedestroy($image);

            return $result;
        } catch (\Exception $e) {
            Log::error('Image optimization failed: ' . $e->getMessage());
            return $imageData;
        }
    }

    /**
     * Create thumbnail from image data
     */
    public function createThumbnail(string $imageData): string
    {
        try {
            $image = imagecreatefromstring($imageData);

            if (!$image) {
                return $imageData;
            }

            $width = imagesx($image);
            $height = imagesy($image);

            // Calculate thumbnail dimensions maintaining aspect ratio
            $ratio = min($this->thumbnailWidth / $width, $this->thumbnailHeight / $height);
            $newWidth = (int) ($width * $ratio);
            $newHeight = (int) ($height * $ratio);

            $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            ob_start();
            imagejpeg($thumbnail, null, 75);
            $result = ob_get_clean();

            imagedestroy($image);
            imagedestroy($thumbnail);

            return $result;
        } catch (\Exception $e) {
            Log::error('Thumbnail creation failed: ' . $e->getMessage());
            return $imageData;
        }
    }

    /**
     * Set max dimensions
     */
    public function setMaxDimensions(int $width, int $height): self
    {
        $this->maxWidth = $width;
        $this->maxHeight = $height;
        return $this;
    }

    /**
     * Set quality
     */
    public function setQuality(int $quality): self
    {
        $this->quality = min(100, max(1, $quality));
        return $this;
    }
}
