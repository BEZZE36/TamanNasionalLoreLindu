<?php

namespace Database\Seeders;

use App\Models\Gallery;
use App\Models\Destination;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        // Get destinations for linking gallery items
        $destinations = Destination::pluck('id', 'slug')->toArray();

        // Flora-based gallery items (nature photography)
        $floraGallery = [
            ['title' => 'Anggrek Langka TNLL', 'image_path' => 'flora/fl1.jpg', 'description' => 'Koleksi anggrek endemik di Taman Nasional Lore Lindu'],
            ['title' => 'Kantong Semar', 'image_path' => 'flora/fl2.jpg', 'description' => 'Tumbuhan karnivora unik di habitat aslinya'],
            ['title' => 'Bunga Raflesia Mekar', 'image_path' => 'flora/fl3.jpg', 'description' => 'Momen langka bunga raflesia mekar di TNLL'],
            ['title' => 'Hutan Lumut Tambing', 'image_path' => 'flora/fl8.jpg', 'description' => 'Pemandangan hutan lumut di sekitar Danau Tambing'],
            ['title' => 'Eboni Sulawesi', 'image_path' => 'flora/fl9.jpg', 'description' => 'Kayu hitam endemik yang dilindungi'],
        ];

        // Fauna-based gallery items (wildlife photography)  
        $faunaGallery = [
            ['title' => 'Anoa di Habitat Asli', 'image_path' => 'fauna/fa1.jpg', 'description' => 'Kerbau terkecil dunia yang hanya ada di Sulawesi'],
            ['title' => 'Tarsius Malam Hari', 'image_path' => 'fauna/fa2.jpg', 'description' => 'Primata terkecil dengan mata besar'],
            ['title' => 'Burung Maleo', 'image_path' => 'fauna/fa3.jpg', 'description' => 'Burung endemik yang menetaskan telur dengan panas bumi'],
            ['title' => 'Babirusa Jantan', 'image_path' => 'fauna/fa4.jpg', 'description' => 'Babi unik dengan taring melengkung'],
            ['title' => 'Monyet Tonkean', 'image_path' => 'fauna/fa5.jpg', 'description' => 'Monyet hitam endemik Sulawesi Tengah'],
            ['title' => 'Kupu-kupu Raksasa', 'image_path' => 'fauna/fa11.jpg', 'description' => 'Kupu-kupu sayap burung yang cantik'],
            ['title' => 'Kakatua Jambul Kuning', 'image_path' => 'fauna/fa21.jpg', 'description' => 'Burung cerdas yang terancam punah'],
        ];

        $sortOrder = 0;

        // Create flora gallery items
        foreach ($floraGallery as $item) {
            Gallery::create([
                'title' => $item['title'],
                'description' => $item['description'],
                'image_path' => $item['image_path'],
                'type' => 'image',
                'is_featured' => $sortOrder < 3,
                'is_active' => true,
                'sort_order' => $sortOrder++,
            ]);
        }

        // Create fauna gallery items
        foreach ($faunaGallery as $item) {
            Gallery::create([
                'title' => $item['title'],
                'description' => $item['description'],
                'image_path' => $item['image_path'],
                'type' => 'image',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => $sortOrder++,
            ]);
        }

        // Add video gallery item
        Gallery::create([
            'title' => 'Keindahan Taman Nasional Lore Lindu',
            'description' => 'Video perjalanan menjelajahi keindahan alam TNLL, dari Danau Tambing hingga Lembah Bada',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', // Example video
            'type' => 'video',
            'destination_id' => $destinations['danau-tambing'] ?? null,
            'is_featured' => true,
            'is_active' => true,
            'sort_order' => $sortOrder++,
        ]);

        Gallery::create([
            'title' => 'Eksplorasi Danau Lindu',
            'description' => 'Dokumentasi perjalanan ke Danau Lindu dan kehidupan masyarakat Lindu',
            'video_url' => 'https://www.youtube.com/watch?v=xyz123', // Example video
            'type' => 'video',
            'destination_id' => $destinations['danau-lindu'] ?? null,
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => $sortOrder++,
        ]);
    }
}
