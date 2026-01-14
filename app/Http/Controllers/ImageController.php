<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\DestinationImage;
use App\Models\Fauna;
use App\Models\FaunaImage;
use App\Models\Flora;
use App\Models\FloraImage;
use App\Models\Gallery;
use App\Models\Upload;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ImageController extends Controller
{
    /**
     * Serve flora image from database
     */
    public function flora(int $id): Response
    {
        $flora = Flora::findOrFail($id);

        if (!$flora->image_data) {
            return $this->placeholder();
        }

        return ImageService::serve($flora->image_data, $flora->image_mime ?? 'image/jpeg', 'flora-' . $id);
    }

    /**
     * Serve flora gallery image from database
     */
    public function floraGallery(int $id): Response
    {
        $image = FloraImage::findOrFail($id);

        if (!$image->image_data) {
            return $this->placeholder();
        }

        return ImageService::serve($image->image_data, $image->image_mime ?? 'image/jpeg', 'flora-gallery-' . $id);
    }

    /**
     * Serve fauna image from database
     */
    public function fauna(int $id): Response
    {
        $fauna = Fauna::findOrFail($id);

        if (!$fauna->image_data) {
            return $this->placeholder();
        }

        return ImageService::serve($fauna->image_data, $fauna->image_mime ?? 'image/jpeg', 'fauna-' . $id);
    }

    /**
     * Serve fauna gallery image from database
     */
    public function faunaGallery(int $id): Response
    {
        $image = FaunaImage::findOrFail($id);

        if (!$image->image_data) {
            return $this->placeholder();
        }

        return ImageService::serve($image->image_data, $image->image_mime ?? 'image/jpeg', 'fauna-gallery-' . $id);
    }

    /**
     * Serve gallery image from database
     */
    public function gallery(int $id): Response
    {
        $gallery = Gallery::findOrFail($id);

        if (!$gallery->image_data) {
            return $this->placeholder();
        }

        return ImageService::serve($gallery->image_data, $gallery->image_mime ?? 'image/jpeg', 'gallery-' . $id);
    }

    /**
     * Serve gallery media image from database (for multi-media albums)
     */
    public function galleryMedia(int $id): Response
    {
        $media = \App\Models\GalleryMedia::findOrFail($id);

        if (!$media->image_data) {
            return $this->placeholder();
        }

        // Use stored MIME type if available, fallback to jpeg for older records
        $mime = $media->image_mime ?? 'image/jpeg';
        return ImageService::serve($media->image_data, $mime, 'gallery-media-' . $id);
    }

    /**
     * Serve banner image from database
     */
    public function banner(int $id, ?string $type = 'desktop'): Response
    {
        $banner = Banner::findOrFail($id);

        if ($type === 'mobile' && $banner->mobile_image_data) {
            return ImageService::serve($banner->mobile_image_data, $banner->mobile_image_mime ?? 'image/jpeg', 'banner-mobile-' . $id);
        }

        if (!$banner->image_data) {
            return $this->placeholder();
        }

        return ImageService::serve($banner->image_data, $banner->image_mime ?? 'image/jpeg', 'banner-' . $id);
    }

    /**
     * Serve destination image from database
     */
    public function destinationImage(int $id): Response
    {
        $image = DestinationImage::findOrFail($id);

        if (!$image->image_data) {
            return $this->placeholder();
        }

        return ImageService::serve($image->image_data, $image->image_mime ?? 'image/jpeg', 'dest-' . $id);
    }

    /**
     * Serve uploaded image (TinyMCE) from database by hash
     */
    public function upload(string $hash): Response
    {
        $upload = Upload::where('hash', $hash)->firstOrFail();

        return ImageService::serve($upload->image_data, $upload->image_mime, $hash);
    }

    /**
     * Serve article image from database
     */
    public function article(int $id): Response
    {
        $article = \App\Models\Article::findOrFail($id);

        if (!$article->image_data) {
            return $this->placeholder();
        }

        return ImageService::serve($article->image_data, $article->image_mime ?? 'image/jpeg', 'article-' . $id);
    }

    /**
     * Return placeholder image
     */
    protected function placeholder(): Response
    {
        $placeholderPath = public_path('images/placeholder-no-image.svg');

        if (file_exists($placeholderPath)) {
            $contents = file_get_contents($placeholderPath);
            return response($contents, 200, [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        // Return a 1x1 transparent pixel as ultimate fallback
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return response($pixel, 200, [
            'Content-Type' => 'image/gif',
        ]);
    }
}
