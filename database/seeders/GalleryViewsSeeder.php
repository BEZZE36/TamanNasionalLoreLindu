<?php

namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;

class GalleryViewsSeeder extends Seeder
{
    /**
     * Seed sample view counts for galleries.
     */
    public function run(): void
    {
        $galleries = Gallery::all();

        foreach ($galleries as $gallery) {
            $gallery->update([
                'view_count' => rand(50, 500)
            ]);
        }

        $this->command->info('Seeded view counts for ' . $galleries->count() . ' galleries.');
        $this->command->info('Total views: ' . Gallery::sum('view_count'));
    }
}
