<?php

namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;

class HomepageGallerySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing galleries to avoid duplicates if re-seeding
        Gallery::truncate();

        // Read logo.png as placeholder image
        $logoPath = public_path('assets/logo.png');
        $logoData = null;
        $logoMime = 'image/png';
        
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
        }

        $galleries = [
            // Row 1
            [
                'title' => 'Danau Tambing',
                'location' => 'Lembah Bada, Sulawesi Tengah',
                'description' => 'Danau Tambing adalah danau vulkanik yang terletak di ketinggian 1.700 mdpl di dalam kawasan Taman Nasional Lore Lindu. Danau ini memiliki luas sekitar 3,5 hektar dengan kedalaman mencapai 14 meter.',
                'category' => 'Danau',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Danau Lindu',
                'location' => 'Kecamatan Lindu, Sigi',
                'description' => 'Danau Lindu adalah danau tektonik terbesar di Sulawesi Tengah dengan luas 3.175 hektar. Terletak di ketinggian 1.000 mdpl, danau ini dikelilingi pegunungan dan hutan hujan tropis.',
                'category' => 'Danau',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Lembah Bada',
                'location' => 'Kabupaten Poso',
                'description' => 'Lembah Bada merupakan salah satu lembah terindah di Sulawesi dengan pemandangan sawah bertingkat, pegunungan hijau, dan situs megalitik bersejarah.',
                'category' => 'Lembah',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Air Panas Kamarora',
                'location' => 'Kamarora, Sigi',
                'description' => 'Pemandian air panas alami yang bersumber dari aktivitas geothermal di kawasan Taman Nasional Lore Lindu. Air panasnya dipercaya memiliki khasiat untuk kesehatan kulit dan relaksasi tubuh.',
                'category' => 'Wisata Air',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 4,
            ],
            // Row 2
            [
                'title' => 'Patung Megalitik',
                'location' => 'Lembah Bada & Napu',
                'description' => 'Patung-patung megalitik di Lembah Bada dan Napu merupakan peninggalan prasejarah berusia lebih dari 5.000 tahun. Terdapat lebih dari 400 patung batu tersebar di lembah ini.',
                'category' => 'Sejarah',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 5,
            ],
            [
                'title' => 'Sunset Danau Lindu',
                'location' => 'Danau Lindu',
                'description' => 'Pemandangan matahari terbenam di Danau Lindu menghadirkan panorama yang memukau dengan siluet pegunungan dan pantulan cahaya keemasan di permukaan air danau yang tenang.',
                'category' => 'Panorama',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 6,
            ],
            [
                'title' => 'Sawah Bertingkat',
                'location' => 'Lembah Bada',
                'description' => 'Hamparan sawah bertingkat di Lembah Bada menciptakan pemandangan yang spektakuler, terutama saat musim tanam. Sistem irigasi tradisional yang masih digunakan menjadi bukti kearifan lokal.',
                'category' => 'Lansekap',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 7,
            ],
            [
                'title' => 'Hutan Hujan Tropis',
                'location' => 'TNLL',
                'description' => 'Hutan hujan tropis TNLL memiliki luas 217.991 hektar dengan lebih dari 2.290 spesies tumbuhan. Keanekaragaman hayatinya menjadikannya salah satu hotspot biodiversitas paling penting di dunia.',
                'category' => 'Hutan',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 8,
            ],
            // Row 3
            [
                'title' => 'Refleksi Awan',
                'location' => 'Danau Tambing',
                'description' => 'Permukaan Danau Tambing yang tenang seperti cermin raksasa memantulkan langit dan awan dengan sempurna. Fenomena ini paling indah terlihat pada pagi hari.',
                'category' => 'Danau',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 9,
            ],
            [
                'title' => 'Anoa',
                'location' => 'TNLL',
                'description' => 'Anoa adalah kerbau kerdil endemik Sulawesi dengan tinggi hanya 75-90 cm. Populasinya diperkirakan kurang dari 5.000 ekor dan termasuk spesies terancam punah.',
                'category' => 'Fauna',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 10,
            ],
            [
                'title' => 'Kaloa/Palindo',
                'location' => 'Lembah Bada',
                'description' => 'Patung Kaloa atau Palindo adalah patung megalitik tertinggi di Lembah Bada dengan tinggi mencapai 4 meter. Patung berbentuk manusia ini dipercaya sebagai penjaga lembah.',
                'category' => 'Sejarah',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 11,
            ],
            [
                'title' => 'Maleo',
                'location' => 'TNLL',
                'description' => 'Burung Maleo adalah burung endemik Sulawesi yang unik karena mengubur telurnya di tanah panas untuk dierami. TNLL merupakan salah satu habitat terakhir bagi burung langka ini.',
                'category' => 'Fauna',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 12,
            ],
            // Row 4
            [
                'title' => 'Gunung Nokilalaki',
                'location' => '2.355 mdpl',
                'description' => 'Gunung Nokilalaki menawarkan pemandangan sunrise spektakuler di atas awan. Pendakian membutuhkan waktu 6-8 jam melalui jalur hutan.',
                'category' => 'Gunung',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 13,
            ],
            [
                'title' => 'Desa Adat Lindu',
                'location' => 'Kecamatan Lindu',
                'description' => 'Masyarakat Lindu masih mempertahankan tradisi dan budaya leluhur dengan rumah adat khas Sulawesi. Upacara tradisional dan kerajinan tangan menjadi daya tarik budaya yang unik.',
                'category' => 'Budaya',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 14,
            ],
            [
                'title' => 'Tarian Tradisional',
                'location' => 'Lembah Bada',
                'description' => 'Tarian tradisional masyarakat Bada menggambarkan hubungan harmonis manusia dengan alam. Tarian ini sering ditampilkan dalam upacara adat dan festival budaya.',
                'category' => 'Budaya',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 15,
            ],
            [
                'title' => 'Flora Endemik',
                'location' => 'TNLL',
                'description' => 'TNLL adalah rumah bagi 227 spesies anggrek dan berbagai tumbuhan endemik Sulawesi. Kekayaan flora ini menjadikan taman nasional sebagai laboratorium alam untuk penelitian botani.',
                'category' => 'Flora',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 16,
            ],
            // Row 5
            [
                'title' => 'Kabut Pagi',
                'location' => 'Danau Tambing',
                'description' => 'Kabut pagi yang menyelimuti Danau Tambing menciptakan suasana mistis dan magis. Pemandangan ini paling indah terlihat antara pukul 05.00-07.00 saat matahari mulai menyinari permukaan danau.',
                'category' => 'Panorama',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 17,
            ],
            [
                'title' => 'Perahu Tradisional',
                'location' => 'Danau Lindu',
                'description' => 'Perahu tradisional masyarakat Lindu masih digunakan untuk mencari ikan dan transportasi antar desa di sekitar danau. Aktivitas nelayan di pagi hari menjadi pemandangan yang menarik.',
                'category' => 'Budaya',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 18,
            ],
            [
                'title' => 'Air Terjun',
                'location' => 'TNLL',
                'description' => 'Berbagai air terjun tersembunyi di dalam kawasan TNLL menawarkan keindahan alam yang masih alami. Beberapa air terjun dapat dicapai melalui jalur trekking dengan pemandangan hutan hujan tropis.',
                'category' => 'Air Terjun',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 19,
            ],
            [
                'title' => 'Trekking Adventure',
                'location' => 'TNLL',
                'description' => 'TNLL menawarkan berbagai jalur trekking dengan tingkat kesulitan bervariasi. Dari jalur santai 2 jam hingga ekspedisi multi-hari untuk menjelajahi keindahan alam.',
                'category' => 'Adventure',
                'image_data' => $logoData,
                'image_mime' => $logoMime,
                'type' => 'image',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 20,
            ],
        ];

        foreach ($galleries as $gallery) {
            Gallery::create($gallery);
        }
    }
}
