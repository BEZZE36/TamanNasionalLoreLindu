<?php

namespace Database\Seeders;

use App\Models\Gallery;
use App\Models\GalleryMedia;
use Illuminate\Database\Seeder;

class MigrateGalleryMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This migrates existing gallery data (single media) to the new gallery_media table.
     */
    public function run(): void
    {
        $galleries = Gallery::all();

        foreach ($galleries as $gallery) {
            // Skip if already has media items (avoid duplicates on re-run)
            if ($gallery->media()->exists()) {
                continue;
            }

            // Only migrate if there's actual media data
            if ($gallery->image_data || $gallery->video_url) {
                GalleryMedia::create([
                    'gallery_id' => $gallery->id,
                    'type' => $gallery->type ?? 'image',
                    'image_data' => $gallery->image_data,
                    'video_url' => $gallery->video_url,
                    'alt_text' => $gallery->title, // Use title as initial alt text
                    'sort_order' => 0,
                ]);
            }
        }

        $this->command->info('Migrated ' . $galleries->count() . ' gallery items to gallery_media table.');
    }
}
