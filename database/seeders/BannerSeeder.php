<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        // Hero banner with Danau Tambing
        Banner::create([
            'title' => 'Selamat Datang di TNLL Explore',
            'subtitle' => 'Jelajahi Keajaiban Taman Nasional Lore Lindu',
            'image_path' => 'assets/danautambing.jpeg',
            'mobile_image_path' => 'assets/danautambing.jpeg',
            'link_url' => '/destinations',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        // Anoa promotional banner
        Banner::create([
            'title' => 'Bertemu Anoa',
            'subtitle' => 'Kerbau Terkecil di Dunia',
            'image_path' => 'assets/fauna/fa1.jpg',
            'mobile_image_path' => 'assets/fauna/fa1.jpg',
            'link_url' => '/destinations/danau-lindu',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Tarsius promotional banner
        Banner::create([
            'title' => 'Temui Tarsius',
            'subtitle' => 'Primata Bermata Besar',
            'image_path' => 'assets/fauna/fa2.jpg',
            'mobile_image_path' => 'assets/fauna/fa2.jpg',
            'link_url' => '/destinations',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Flora promotional banner
        Banner::create([
            'title' => 'Hutan Tropis Sulawesi',
            'subtitle' => 'Keanekaragaman Flora Endemik',
            'image_path' => 'assets/flora/fl5.jpg',
            'mobile_image_path' => 'assets/flora/fl5.jpg',
            'link_url' => '/destinations/danau-tambing',
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }
}
