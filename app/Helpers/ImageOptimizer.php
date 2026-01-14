<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImageOptimizer
{
    /**
     * Optimize and optionally convert image to WebP format.
     * 
     * @param string $path Path to the original image
     * @param int $quality Quality for compression (1-100)
     * @param bool $convertToWebp Whether to convert to WebP
     * @return string|null Path to optimized image
     */
    public static function optimize(string $path, int $quality = 80, bool $convertToWebp = true): ?string
    {
        try {
            if (!Storage::disk('public')->exists($path)) {
                return null;
            }

            $fullPath = Storage::disk('public')->path($path);

            // Get image info
            $imageInfo = @getimagesize($fullPath);
            if (!$imageInfo) {
                return null;
            }

            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $mimeType = $imageInfo['mime'];

            // Create image resource based on type
            $image = self::createImageFromFile($fullPath, $mimeType);
            if (!$image) {
                return null;
            }

            // Resize if too large (max 1920px width)
            if ($width > 1920) {
                $newWidth = 1920;
                $newHeight = (int) ($height * ($newWidth / $width));
                $resized = imagecreatetruecolor($newWidth, $newHeight);

                // Preserve transparency for PNG
                if ($mimeType === 'image/png') {
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                }

                imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $resized;
            }

            if ($convertToWebp && function_exists('imagewebp')) {
                // Generate WebP path
                $webpPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $path);
                $webpFullPath = Storage::disk('public')->path($webpPath);

                // Save as WebP
                imagewebp($image, $webpFullPath, $quality);
                imagedestroy($image);

                return $webpPath;
            }

            // Just optimize the original
            self::saveImage($image, $fullPath, $mimeType, $quality);
            imagedestroy($image);

            return $path;

        } catch (\Exception $e) {
            Log::error('Image optimization failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create GD image resource from file.
     */
    private static function createImageFromFile(string $path, string $mimeType)
    {
        return match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/gif' => @imagecreatefromgif($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            default => null,
        };
    }

    /**
     * Save GD image resource to file.
     */
    private static function saveImage($image, string $path, string $mimeType, int $quality): bool
    {
        return match ($mimeType) {
            'image/jpeg' => imagejpeg($image, $path, $quality),
            'image/png' => imagepng($image, $path, (int) (9 - ($quality / 10))),
            'image/gif' => imagegif($image, $path),
            'image/webp' => function_exists('imagewebp') ? imagewebp($image, $path, $quality) : false,
            default => false,
        };
    }

    /**
     * Get WebP version of an image if it exists, otherwise return original.
     */
    public static function getOptimizedUrl(string $path): string
    {
        if (empty($path)) {
            return asset('assets/logo.png');
        }

        // Check if WebP version exists
        $webpPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $path);

        if (Storage::disk('public')->exists($webpPath)) {
            return asset('storage/' . $webpPath);
        }

        // Return original
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return asset('storage/' . $path);
    }

    /**
     * Generate responsive image srcset.
     */
    public static function generateSrcset(string $path, array $widths = [320, 640, 768, 1024, 1280]): string
    {
        $srcset = [];

        foreach ($widths as $width) {
            $srcset[] = self::getOptimizedUrl($path) . " {$width}w";
        }

        return implode(', ', $srcset);
    }
}
