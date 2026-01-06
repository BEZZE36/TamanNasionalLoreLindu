<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'title' => 'Danau Tambing: Destinasi Favorit Wisatawan Mancanegara',
                'slug' => 'danau-tambing-destinasi-favorit-wisatawan-mancanegara',
                'excerpt' => 'Danau Tambing terus menarik wisatawan dari berbagai negara dengan keindahan alamnya yang memukau.',
                'content' => "Danau Tambing, salah satu permata tersembunyi di Taman Nasional Lore Lindu, terus memikat hati wisatawan dari berbagai penjuru dunia. Terletak di ketinggian 1.700 mdpl, danau ini menawarkan pemandangan yang menakjubkan dengan air jernih yang dikelilingi hutan hujan tropis.

Pihak pengelola baru saja memperbarui fasilitas camping dan trekking untuk meningkatkan pengalaman wisatawan. Kini tersedia area camping yang lebih luas dengan fasilitas toilet dan tempat bilas yang bersih.

\"Kami berkomitmen untuk menjaga kelestarian alam sambil memberikan pengalaman terbaik bagi pengunjung,\" ujar Kepala Balai Besar TNLL.

Danau Tambing juga menjadi habitat bagi berbagai spesies burung endemik Sulawesi, menjadikannya surga bagi para pengamat burung. Wisatawan dapat menikmati aktivitas bird watching di pagi hari ketika burung-burung mulai aktif mencari makan.

Tips berkunjung:
- Waktu terbaik: April - Oktober (musim kemarau)
- Bawa pakaian hangat karena suhu bisa mencapai 15°C
- Sewa pemandu lokal untuk pengalaman terbaik
- Jaga kebersihan dan bawa sampah pulang",
                'featured_image' => 'destinations/danautambing.png',
                'category' => 'wisata',
                'author_name' => 'Tim TNLL Explore',
                'is_published' => true,
                'is_featured' => true,
                'published_at' => now()->subDays(2),
                'views_count' => 1250,
            ],
            [
                'title' => 'Penemuan Baru Situs Megalitikum di Lembah Bada',
                'slug' => 'penemuan-baru-situs-megalitikum-lembah-bada',
                'excerpt' => 'Tim arkeolog menemukan situs megalitikum baru yang diperkirakan berusia lebih dari 5000 tahun.',
                'content' => "Kabar menggembirakan datang dari tim arkeolog yang sedang melakukan penelitian di Lembah Bada. Mereka berhasil menemukan situs megalitikum baru yang diperkirakan berusia lebih dari 5000 tahun.

Penemuan ini menambah kekayaan warisan budaya Taman Nasional Lore Lindu yang sudah dikenal dengan patung-patung batu purba seperti Patung Palindo (Patung Selamat Datang) dan berbagai artefak prasejarah lainnya.

Situs baru ini ditemukan di bagian timur Lembah Bada, tidak jauh dari permukiman penduduk lokal. Tim peneliti dari Balai Arkeologi Sulawesi Selatan dan Universitas Tadulako telah mendokumentasikan temuan ini.

\"Penemuan ini membuka wawasan baru tentang peradaban kuno di Sulawesi Tengah. Patung-patung ini memiliki karakteristik unik yang berbeda dari temuan sebelumnya,\" jelas Dr. Andi Makkulau, kepala tim peneliti.

Lembah Bada, bersama dengan Lembah Besoa dan Napu, dikenal sebagai \"Tiga Lembah Megalitikum\" yang menyimpan ratusan patung batu purba dengan berbagai bentuk dan ukuran. UNESCO sedang mempertimbangkan kawasan ini sebagai Warisan Dunia.",
                'featured_image' => 'destinations/lembahbada.jpg',
                'category' => 'budaya',
                'author_name' => 'Tim TNLL Explore',
                'is_published' => true,
                'is_featured' => true,
                'published_at' => now()->subDays(5),
                'views_count' => 890,
            ],
            [
                'title' => 'Program Konservasi Anoa Berhasil Tingkatkan Populasi',
                'slug' => 'program-konservasi-anoa-tingkatkan-populasi',
                'excerpt' => 'Program konservasi selama 5 tahun berhasil meningkatkan populasi Anoa sebesar 15%.',
                'content' => "Program konservasi yang dijalankan Balai Besar Taman Nasional Lore Lindu selama 5 tahun terakhir membuahkan hasil menggembirakan. Populasi Anoa (Bubalus depressicornis), satwa endemik Sulawesi yang terancam punah, berhasil meningkat sebesar 15%.

Berdasarkan survei terbaru menggunakan kamera trap dan metode transek, diperkirakan terdapat sekitar 250-300 individu Anoa yang hidup di kawasan TNLL. Angka ini meningkat signifikan dibandingkan survei 5 tahun lalu yang hanya mencatat sekitar 220 individu.

Program konservasi ini meliputi:
1. Patroli anti-perburuan rutin
2. Rehabilitasi habitat
3. Edukasi masyarakat sekitar
4. Pemantauan kesehatan populasi
5. Kolaborasi dengan lembaga konservasi internasional

\"Keberhasilan ini tidak lepas dari dukungan masyarakat sekitar yang turut menjaga kelestarian Anoa,\" ungkap Kepala Seksi Konservasi TNLL.

Selain Anoa, program konservasi juga berdampak positif pada satwa lain seperti Tarsius, Maleo, dan berbagai spesies burung endemik lainnya. TNLL menjadi contoh sukses konservasi yang bisa ditiru oleh taman nasional lain di Indonesia.",
                'featured_image' => 'destinations/danaulindu.jpg',
                'category' => 'konservasi',
                'author_name' => 'Tim TNLL Explore',
                'is_published' => true,
                'is_featured' => true,
                'published_at' => now()->subDays(7),
                'views_count' => 2100,
            ],
            [
                'title' => 'Workshop Fotografi Alam di TNLL',
                'slug' => 'workshop-fotografi-alam-tnll',
                'excerpt' => 'TNLL mengadakan workshop fotografi alam gratis untuk pemuda lokal dan pecinta alam.',
                'content' => "Dalam rangka memperingati Hari Konservasi Alam Nasional, Balai Besar TNLL mengadakan workshop fotografi alam gratis yang diikuti oleh 50 peserta dari berbagai kalangan.

Workshop berlangsung selama 3 hari di kawasan Danau Tambing dengan materi meliputi:
- Teknik dasar fotografi wildlife
- Penggunaan kamera DSLR dan mirrorless
- Tips memotret burung dan satwa liar
- Etika fotografi alam
- Post-processing dengan Lightroom

Peserta juga mendapat kesempatan untuk berlatih langsung di lapangan dengan bimbingan fotografer profesional. Hasil karya terbaik akan dipamerkan di website resmi TNLL dan media sosial.

\"Kami berharap workshop ini bisa melahirkan fotografer-fotografer alam yang akan membantu mempromosikan keindahan TNLL ke seluruh dunia,\" kata koordinator acara.

Workshop serupa akan diadakan secara berkala setiap 6 bulan. Informasi pendaftaran akan diumumkan melalui website dan media sosial TNLL.",
                'featured_image' => 'destinations/airpanaskamarora.jpg',
                'category' => 'event',
                'author_name' => 'Tim TNLL Explore',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => now()->subDays(10),
                'views_count' => 650,
            ],
            [
                'title' => 'Mengenal Maleo: Burung Unik yang Hanya Ada di Sulawesi',
                'slug' => 'mengenal-maleo-burung-unik-sulawesi',
                'excerpt' => 'Maleo adalah burung endemik Sulawesi dengan cara berkembang biak yang unik menggunakan panas geotermal.',
                'content' => "Maleo (Macrocephalon maleo) adalah salah satu burung paling unik di dunia yang hanya ditemukan di Sulawesi. Burung ini memiliki cara berkembang biak yang sangat tidak biasa - mereka menetaskan telur menggunakan panas geotermal atau sinar matahari!

Karakteristik Maleo:
- Ukuran mirip ayam dengan tinggi sekitar 55 cm
- Kepala berwarna hitam dengan tonjolan seperti tanduk
- Bulu tubuh berwarna hitam dengan dada merah muda
- Kaki besar dan kuat untuk menggali

Berbeda dengan burung lain yang mengerami telurnya, Maleo menggali lubang di pasir panas dekat sumber geotermal atau di pantai berpasir hitam. Telur ditanam sedalam 30-50 cm dan dibiarkan menetas sendiri selama 60-80 hari.

Di TNLL, terdapat beberapa lokasi peneluran Maleo yang dilindungi. Pengunjung bisa menyaksikan aktivitas Maleo di pagi hari ketika mereka datang untuk bertelur.

Status konservasi: Terancam Punah (Endangered)
Populasi di TNLL: Diperkirakan 300-500 individu",
                'featured_image' => 'destinations/danautambing.png',
                'category' => 'edukasi',
                'author_name' => 'Tim TNLL Explore',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => now()->subDays(14),
                'views_count' => 1800,
            ],
            [
                'title' => 'Perubahan Jam Operasional Pos Jaga TNLL',
                'slug' => 'perubahan-jam-operasional-pos-jaga-tnll',
                'excerpt' => 'Mulai Januari 2025, jam operasional pos jaga akan diperpanjang untuk mengakomodasi permintaan pengunjung.',
                'content' => "Menanggapi masukan dari pengunjung, Balai Besar TNLL mengumumkan perubahan jam operasional pos jaga yang akan berlaku mulai 1 Januari 2025.

Jam Operasional Baru:
- Pos Jaga Utama: 06.00 - 18.00 WITA (sebelumnya 07.00 - 17.00)
- Pos Jaga Danau Tambing: 05.30 - 18.30 WITA
- Pos Jaga Lembah Bada: 06.30 - 17.30 WITA

Perubahan ini dilakukan untuk:
1. Mengakomodasi wisatawan yang ingin menikmati sunrise
2. Memberikan waktu lebih bagi fotografer alam
3. Mengurangi antrian di pagi hari

Harap diperhatikan:
- Pendaftaran online tetap disarankan
- Pemandu wajib untuk jalur tertentu
- Maksimal 50 orang per jam untuk jalur populer

Untuk informasi lebih lanjut, hubungi:
- Telepon: (0451) 123456
- Email: info@tnll.go.id
- WhatsApp: 0812-3456-7890",
                'featured_image' => 'destinations/lembahbada.jpg',
                'category' => 'pengumuman',
                'author_name' => 'Tim TNLL Explore',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => now()->subDays(3),
                'views_count' => 320,
            ],
        ];

        foreach ($articles as $article) {
            Article::create($article);
        }
    }
}
