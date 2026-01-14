<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    protected int $maxWidth = 1920;
    protected int $maxHeight = 1080;
    protected int $quality = 80;
    protected bool $convertToWebp = true;

    /**
     * Process and optimize an uploaded image
     */
    public function optimize(UploadedFile $file, string $directory = 'images', ?array $options = []): string
    {
        $this->applyOptions($options);

        $filename = $this->generateFilename($file);
        $path = $directory . '/' . $filename;

        // Read image
        try {
            $image = Image::read($file);

            // Resize if larger than max dimensions
            $image->scaleDown(width: $this->maxWidth, height: $this->maxHeight);

            // Encode with quality
            if ($this->convertToWebp && $file->getClientOriginalExtension() !== 'gif') {
                $encoded = $image->toWebp($this->quality);
                $path = preg_replace('/\.[^.]+$/', '.webp', $path);
            } else {
                $encoded = $image->toJpeg($this->quality);
            }

            // Store
            Storage::disk('public')->put($path, $encoded);

            return $path;
        } catch (\Exception $e) {
            // Fallback: store original file
            return $file->store($directory, 'public');
        }
    }

    /**
     * Create a thumbnail
     */
    public function thumbnail(UploadedFile $file, string $directory = 'thumbnails', int $width = 300, int $height = 200): string
    {
        $filename = 'thumb_' . $this->generateFilename($file);
        $path = $directory . '/' . $filename;

        try {
            $image = Image::read($file);
            $image->cover($width, $height);

            if ($this->convertToWebp) {
                $encoded = $image->toWebp($this->quality);
                $path = preg_replace('/\.[^.]+$/', '.webp', $path);
            } else {
                $encoded = $image->toJpeg($this->quality);
            }

            Storage::disk('public')->put($path, $encoded);

            return $path;
        } catch (\Exception $e) {
            return $file->store($directory, 'public');
        }
    }

    /**
     * Optimize existing image from path
     */
    public function optimizeExisting(string $path): bool
    {
        try {
            $fullPath = Storage::disk('public')->path($path);
            
            if (!file_exists($fullPath)) {
                return false;
            }

            $image = Image::read($fullPath);
            $image->scaleDown(width: $this->maxWidth, height: $this->maxHeight);

            if ($this->convertToWebp) {
                $newPath = preg_replace('/\.[^.]+$/', '.webp', $fullPath);
                $image->toWebp($this->quality)->save($newPath);
                
                // Delete original if different extension
                if ($newPath !== $fullPath) {
                    unlink($fullPath);
                }
            } else {
                $image->toJpeg($this->quality)->save($fullPath);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generate unique filename
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $this->convertToWebp ? 'webp' : $file->getClientOriginalExtension();
        return time() . '_' . uniqid() . '.' . $extension;
    }

    /**
     * Apply options
     */
    protected function applyOptions(?array $options): void
    {
        if (isset($options['maxWidth'])) {
            $this->maxWidth = $options['maxWidth'];
        }
        if (isset($options['maxHeight'])) {
            $this->maxHeight = $options['maxHeight'];
        }
        if (isset($options['quality'])) {
            $this->quality = $options['quality'];
        }
        if (isset($options['webp'])) {
            $this->convertToWebp = $options['webp'];
        }
    }

    /**
     * Set quality
     */
    public function setQuality(int $quality): self
    {
        $this->quality = min(100, max(1, $quality));
        return $this;
    }

    /**
     * Disable WebP conversion
     */
    public function noWebp(): self
    {
        $this->convertToWebp = false;
        return $this;
    }

    /**
     * Set max dimensions
     */
    public function maxDimensions(int $width, int $height): self
    {
        $this->maxWidth = $width;
        $this->maxHeight = $height;
        return $this;
    }

    // ============================================================
    // DATABASE STORAGE METHODS (Static)
    // ============================================================

    /**
     * Store image from uploaded file - converts to base64
     */
    public static function storeToDatabase(UploadedFile $file): array
    {
        $contents = file_get_contents($file->getRealPath());
        
        return [
            'data' => base64_encode($contents),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'filename' => $file->getClientOriginalName(),
        ];
    }

    /**
     * Store image from existing file path (storage or public/assets)
     */
    public static function storeFromPath(string $path): ?array
    {
        $contents = null;
        $mime = null;

        // Try storage first
        if (Storage::disk('public')->exists($path)) {
            $contents = Storage::disk('public')->get($path);
            $mime = Storage::disk('public')->mimeType($path);
        }
        // Try public/assets
        elseif (file_exists(public_path('assets/' . $path))) {
            $fullPath = public_path('assets/' . $path);
            $contents = file_get_contents($fullPath);
            $mime = mime_content_type($fullPath);
        }
        // Try public/assets with full path
        elseif (file_exists(public_path($path))) {
            $fullPath = public_path($path);
            $contents = file_get_contents($fullPath);
            $mime = mime_content_type($fullPath);
        }
        // Try storage with raw path
        elseif (file_exists(storage_path('app/public/' . $path))) {
            $fullPath = storage_path('app/public/' . $path);
            $contents = file_get_contents($fullPath);
            $mime = mime_content_type($fullPath);
        }

        if (!$contents) {
            return null;
        }

        return [
            'data' => base64_encode($contents),
            'mime' => $mime ?: 'image/jpeg',
            'size' => strlen($contents),
        ];
    }

    /**
     * Serve image from base64 data with proper headers and caching
     */
    public static function serve(string $base64Data, string $mime, ?string $etag = null): \Symfony\Component\HttpFoundation\Response
    {
        $contents = base64_decode($base64Data);
        
        $headers = [
            'Content-Type' => $mime,
            'Content-Length' => strlen($contents),
            'Cache-Control' => 'public, max-age=31536000', // 1 year
            'Pragma' => 'public',
        ];

        if ($etag) {
            $headers['ETag'] = '"' . $etag . '"';
        }

        return response($contents, 200, $headers);
    }

    /**
     * Generate data URI for inline display (useful for small images)
     */
    public static function getDataUri(string $base64Data, string $mime): string
    {
        return "data:{$mime};base64,{$base64Data}";
    }

    /**
     * Generate hash for image data (used as unique identifier)
     */
    public static function generateHash(string $base64Data): string
    {
        return hash('sha256', $base64Data);
    }
}
