<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DestinationCategory;
use Illuminate\Support\Str;

class DestinationCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Wisata Alam',
                'icon' => '🌲',
                'description' => 'Destinasi yang menawarkan keindahan alam, hutan, dan pegunungan.',
            ],
            [
                'name' => 'Danau & Air Terjun',
                'icon' => '💧',
                'description' => 'Wisata perairan seperti danau, sungai, dan air terjun menawan.',
            ],
            [
                'name' => 'Camping Ground',
                'icon' => '⛺',
                'description' => 'Lokasi khusus untuk berkemah dan menikmati alam terbuka.',
            ],
            [
                'name' => 'Situs Budaya',
                'icon' => '🗿',
                'description' => 'Situs megalitikum dan peninggalan sejarah kebudayaan lokal.',
            ],
            [
                'name' => 'Pengamatan Satwa',
                'icon' => '🦅',
                'description' => 'Lokasi terbaik untuk mengamati burung dan satwa endemik.',
            ],
            [
                'name' => 'Wisata Petualangan',
                'icon' => '🧗',
                'description' => 'Aktivitas menantang seperti trekking, hiking, dan panjat tebing.',
            ],
        ];

        foreach ($categories as $category) {
            DestinationCategory::firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                $category
            );
        }
    }
}
