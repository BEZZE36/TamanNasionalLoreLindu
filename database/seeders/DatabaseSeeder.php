<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Destination;
use App\Models\DestinationImage;
use App\Models\DestinationPrice;
use App\Models\Banner;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Roles and Permissions first
        $this->call(RolePermissionSeeder::class);

        // Create Super Admin
        $superAdmin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'username' => 'superadmin',
            'password' => 'Password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // Assign Super Admin role
        $superAdminRole = \App\Models\Role::where('slug', 'super-admin')->first();
        if ($superAdminRole) {
            $superAdmin->roles()->attach($superAdminRole->id);
        }

        // Seed Flora and Fauna from public/assets
        $this->call([
            FloraSeeder::class,
            FaunaSeeder::class,
            GallerySeeder::class,
            BannerSeeder::class,
        ]);

        // =====================================================
        // DESTINATION 1: DANAU TAMBING
        // =====================================================
        $danauTambing = Destination::create([
            'name' => 'Danau Tambing',
            'slug' => 'danau-tambing',
            'description' => 'Danau Tambing adalah sebuah danau cantik yang terletak di kawasan Taman Nasional Lore Lindu, Sulawesi Tengah. Danau ini berada di ketinggian sekitar 1.700 meter di atas permukaan laut, menjadikannya salah satu destinasi wisata alam paling memukau di Indonesia.

Air danau yang jernih berwarna hijau kebiruan mencerminkan keindahan alam sekitarnya, termasuk pepohonan rapat dan langit biru. Suasana tenang dan sejuk khas pegunungan tropis menjadikan tempat ini sempurna untuk healing dan menikmati keindahan alam.

Danau Tambing tidak hanya menjadi daya tarik wisata, tetapi juga memiliki nilai ekologis tinggi sebagai habitat berbagai spesies flora dan fauna endemik Sulawesi.',
            'short_description' => 'Danau eksotis di ketinggian 1.700 mdpl dengan pemandangan hutan pegunungan yang masih asri.',
            'address' => 'Desa Sedoa, Kecamatan Lore Utara',
            'city' => 'Kabupaten Poso',
            'province' => 'Sulawesi Tengah',
            'latitude' => -1.3211,
            'longitude' => 120.1850,
            'operating_hours' => '07:00 - 17:00 WITA',
            'contact_phone' => '+62 451 123456',
            'contact_email' => 'info@tnll-explore.com',
            'facilities' => ['Toilet', 'Warung', 'Parkir Motor & Mobil', 'Gazebo', 'Jembatan Bambu', 'Spot Foto', 'Area Camping'],
            'rules' => [
                'Dilarang membuang sampah sembarangan',
                'Dilarang berenang di danau',
                'Dilarang membawa hewan peliharaan',
                'Wajib menjaga ketenangan',
                'Dilarang memetik tanaman dan mengganggu satwa',
                'Pembayaran tiket non-tunai (cashless)',
            ],
            'is_featured' => true,
            'is_active' => true,
            'rating' => 4.8,
            'review_count' => 256,
            'visitor_count' => 12450,
        ]);

        DestinationImage::create([
            'destination_id' => $danauTambing->id,
            'image_path' => 'destinations/danautambing.png',
            'alt_text' => 'Danau Tambing - Pemandangan danau dengan hutan pegunungan',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        // Harga tiket resmi per PP No. 36/2024 (berlaku 30 Oktober 2024)
        $this->createPrices($danauTambing->id, [
            ['category' => 'adult', 'label' => 'Tiket Dewasa (WNI)', 'desc' => 'Pengunjung usia 12 tahun ke atas', 'price' => 10000, 'weekend' => 15000],
            ['category' => 'child', 'label' => 'Tiket Anak-anak', 'desc' => 'Pengunjung usia 3-11 tahun', 'price' => 5000, 'weekend' => 7500],
            ['category' => 'senior', 'label' => 'Tiket Rombongan Pelajar', 'desc' => 'Minimal 5 orang pelajar/mahasiswa', 'price' => 5000, 'weekend' => 7500],
            ['category' => 'motor', 'label' => 'Parkir Motor', 'desc' => 'Biaya parkir sepeda motor', 'price' => 5000],
            ['category' => 'car', 'label' => 'Parkir Mobil', 'desc' => 'Biaya parkir mobil/roda 4', 'price' => 10000],
            ['category' => 'bus', 'label' => 'Parkir Bus/Minibus', 'desc' => 'Biaya parkir bus/minibus', 'price' => 25000],
        ]);

        // =====================================================
        // DESTINATION 2: DANAU LINDU
        // =====================================================
        $danauLindu = Destination::create([
            'name' => 'Danau Lindu',
            'slug' => 'danau-lindu',
            'description' => 'Danau Lindu adalah danau alami terbesar di Taman Nasional Lore Lindu, terletak di tengah kawasan taman nasional pada ketinggian 1.000 meter. Danau ini dikelilingi oleh pegunungan dan hutan hujan tropis yang masih alami.

Masyarakat suku Lindu yang tinggal di sekitar danau masih mempertahankan tradisi dan kearifan lokal mereka. Anda bisa berinteraksi dengan penduduk, memancing bersama warga lokal, atau menyewa perahu ketinting untuk mengelilingi danau.

Danau Lindu juga merupakan habitat bagi beberapa spesies ikan endemik yang hanya ditemukan di sini.',
            'short_description' => 'Danau alami terbesar di TNLL dengan kehidupan masyarakat adat Lindu yang masih asli.',
            'address' => 'Desa Tomado, Kecamatan Lindu',
            'city' => 'Kabupaten Sigi',
            'province' => 'Sulawesi Tengah',
            'latitude' => -1.3089,
            'longitude' => 120.0667,
            'operating_hours' => '06:00 - 18:00 WITA',
            'contact_phone' => '+62 451 123457',
            'contact_email' => 'info@tnll-explore.com',
            'facilities' => ['Dermaga Perahu', 'Homestay', 'Warung Makan', 'Toilet', 'Spot Memancing', 'Parkir'],
            'rules' => [
                'Hormati adat istiadat masyarakat Lindu',
                'Dilarang membuang sampah ke danau',
                'Sewa perahu melalui koperasi desa',
                'Wajib didampingi pemandu lokal (tracking)',
            ],
            'is_featured' => true,
            'is_active' => true,
            'rating' => 4.7,
            'review_count' => 189,
            'visitor_count' => 8920,
        ]);

        DestinationImage::create([
            'destination_id' => $danauLindu->id,
            'image_path' => 'destinations/danaulindu.jpg',
            'alt_text' => 'Danau Lindu - Pemandangan danau dan desa',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $this->createPrices($danauLindu->id, [
            ['category' => 'adult', 'label' => 'Tiket Dewasa (WNI)', 'desc' => 'Pengunjung usia 12 tahun ke atas', 'price' => 10000, 'weekend' => 15000],
            ['category' => 'child', 'label' => 'Tiket Anak-anak', 'desc' => 'Pengunjung usia 3-11 tahun', 'price' => 5000, 'weekend' => 7500],
            ['category' => 'motor', 'label' => 'Parkir Motor', 'desc' => 'Biaya parkir sepeda motor', 'price' => 5000],
            ['category' => 'car', 'label' => 'Parkir Mobil', 'desc' => 'Biaya parkir mobil', 'price' => 10000],
        ]);

        // =====================================================
        // DESTINATION 3: LEMBAH BADA - SITUS MEGALITIKUM
        // =====================================================
        $lembahBada = Destination::create([
            'name' => 'Lembah Bada - Situs Megalitikum',
            'slug' => 'lembah-bada-megalitikum',
            'description' => 'Lembah Bada adalah salah satu dari tiga lembah di Taman Nasional Lore Lindu yang menyimpan lebih dari 400 patung batu megalitikum kuno. Patung-patung misterius ini diperkirakan berusia lebih dari 1.000 tahun dan menjadi salah satu warisan prasejarah paling penting di Indonesia.

Patung terkenal seperti Palindo (patung megalit berwajah manusia tertinggi) dan berbagai Kalamba (gentong batu raksasa) dapat ditemukan tersebar di lembah ini. Hingga kini, asal-usul dan fungsi patung-patung ini masih menjadi misteri.

Selain situs megalitikum, Lembah Bada juga menawarkan pemandangan sawah terasering dan kehidupan masyarakat adat yang masih tradisional.',
            'short_description' => 'Situs prasejarah dengan 400+ patung megalitikum misterius berusia ribuan tahun.',
            'address' => 'Desa Gintu, Kecamatan Lore Selatan',
            'city' => 'Kabupaten Poso',
            'province' => 'Sulawesi Tengah',
            'latitude' => -1.6667,
            'longitude' => 120.1833,
            'operating_hours' => '07:00 - 17:00 WITA',
            'contact_phone' => '+62 451 123458',
            'contact_email' => 'info@tnll-explore.com',
            'facilities' => ['Pemandu Lokal', 'Homestay', 'Warung', 'Toilet', 'Parkir', 'Pusat Informasi'],
            'rules' => [
                'Wajib menggunakan jasa pemandu lokal',
                'Dilarang menyentuh atau memanjat patung',
                'Dilarang mengambil batu atau artefak',
                'Hormati situs sebagai warisan budaya',
            ],
            'is_featured' => true,
            'is_active' => true,
            'rating' => 4.9,
            'review_count' => 324,
            'visitor_count' => 15680,
        ]);

        DestinationImage::create([
            'destination_id' => $lembahBada->id,
            'image_path' => 'destinations/lembahbada.jpg',
            'alt_text' => 'Lembah Bada - Patung Megalitikum',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $this->createPrices($lembahBada->id, [
            ['category' => 'adult', 'label' => 'Tiket Dewasa (WNI)', 'desc' => 'Pengunjung usia 12 tahun ke atas', 'price' => 10000, 'weekend' => 15000],
            ['category' => 'child', 'label' => 'Tiket Anak-anak', 'desc' => 'Pengunjung usia 3-11 tahun', 'price' => 5000, 'weekend' => 7500],
            ['category' => 'motor', 'label' => 'Parkir Motor', 'desc' => 'Biaya parkir sepeda motor', 'price' => 5000],
            ['category' => 'car', 'label' => 'Parkir Mobil', 'desc' => 'Biaya parkir mobil', 'price' => 10000],
        ]);

        // =====================================================
        // DESTINATION 4: AIR PANAS KAMARORA
        // =====================================================
        $airPanas = Destination::create([
            'name' => 'Air Panas Kamarora',
            'slug' => 'air-panas-kamarora',
            'description' => 'Air Panas Kamarora adalah sumber air panas alami yang terletak di pintu masuk Taman Nasional Lore Lindu. Tempat ini menjadi favorit wisatawan untuk bersantai setelah perjalanan panjang menuju destinasi lain di TNLL.

Air panas dengan suhu sekitar 40-50 derajat Celsius mengandung mineral alami yang dipercaya baik untuk kesehatan kulit dan relaksasi otot. Fasilitas kolam sudah ditata dengan baik sehingga pengunjung dapat berendam dengan nyaman.

Di sekitar lokasi juga dapat ditemukan habitat tarsius (monyet hantu) yang dapat diamati pada malam hari dengan pendampingan guide lokal.',
            'short_description' => 'Pemandian air panas alami dengan mineral berkhasiat di pintu masuk TNLL.',
            'address' => 'Desa Kamarora, Kecamatan Nokilalaki',
            'city' => 'Kabupaten Sigi',
            'province' => 'Sulawesi Tengah',
            'latitude' => -1.2756,
            'longitude' => 120.1234,
            'operating_hours' => '07:00 - 21:00 WITA',
            'contact_phone' => '+62 451 123459',
            'contact_email' => 'info@tnll-explore.com',
            'facilities' => ['Kolam Air Panas', 'Kamar Ganti', 'Toilet', 'Warung', 'Parkir', 'Gazebo'],
            'rules' => [
                'Wajib mandi bilas sebelum masuk kolam',
                'Dilarang menggunakan sabun di kolam',
                'Anak-anak harus didampingi orang tua',
                'Maksimal berendam 30 menit',
            ],
            'is_featured' => false,
            'is_active' => true,
            'rating' => 4.5,
            'review_count' => 156,
            'visitor_count' => 7830,
        ]);

        DestinationImage::create([
            'destination_id' => $airPanas->id,
            'image_path' => 'destinations/airpanaskamarora.jpg',
            'alt_text' => 'Air Panas Kamarora',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $this->createPrices($airPanas->id, [
            ['category' => 'adult', 'label' => 'Tiket Dewasa', 'desc' => 'Pengunjung dewasa', 'price' => 15000, 'weekend' => 20000],
            ['category' => 'child', 'label' => 'Tiket Anak-anak', 'desc' => 'Pengunjung anak-anak', 'price' => 10000, 'weekend' => 12500],
            ['category' => 'motor', 'label' => 'Parkir Motor', 'desc' => 'Biaya parkir sepeda motor', 'price' => 5000],
            ['category' => 'car', 'label' => 'Parkir Mobil', 'desc' => 'Biaya parkir mobil', 'price' => 10000],
        ]);

        // =====================================================
        // DEFAULT SETTINGS
        // =====================================================
        $settings = [
            ['key' => 'site_name', 'value' => 'TNLL Explore', 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'site_description', 'value' => 'Platform E-Ticketing Resmi Wisata Taman Nasional Lore Lindu, Sulawesi Tengah', 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'site_email', 'value' => 'info@tnll-explore.com', 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'site_phone', 'value' => '+62 451 123456', 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'service_fee', 'value' => '2500', 'type' => 'integer', 'group' => 'payment', 'is_public' => true],
            ['key' => 'service_fee_type', 'value' => 'fixed', 'type' => 'string', 'group' => 'payment', 'is_public' => false],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }

        // Create default banner
        Banner::create([
            'title' => 'Selamat Datang di TNLL Explore',
            'subtitle' => 'Jelajahi Keindahan Taman Nasional Lore Lindu, Sulawesi Tengah',
            'image_path' => 'banners/danautambing.jpg',
            'link_url' => '/destinations',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->command->info('');
        $this->command->info('✓ Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Super Admin Account:');
        $this->command->info('  Email: superadmin@gmail.com');
        $this->command->info('  Password: Password123');
        $this->command->info('');
        $this->command->info('Created 4 destinations with real TNLL 2024 prices');
    }

    /**
     * Helper to create destination prices
     */
    private function createPrices(int $destinationId, array $prices): void
    {
        foreach ($prices as $price) {
            DestinationPrice::create([
                'destination_id' => $destinationId,
                'category' => $price['category'],
                'label' => $price['label'],
                'description' => $price['desc'],
                'price' => $price['price'],
                'weekend_price' => $price['weekend'] ?? $price['price'],
                'is_active' => true,
            ]);
        }
    }

    /**
     * Run additional seeders
     */
    public function seedFloraAndFauna(): void
    {
        $this->call([
            FloraSeeder::class,
            FaunaSeeder::class,
        ]);
    }
}

