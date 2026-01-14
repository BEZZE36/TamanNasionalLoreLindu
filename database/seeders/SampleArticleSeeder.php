<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class SampleArticleSeeder extends Seeder
{
    public function run(): void
    {
        Article::create([
            'title' => 'Mengenal Keanekaragaman Hayati Taman Nasional Lore Lindu',
            'slug' => 'mengenal-keanekaragaman-hayati-taman-nasional-lore-lindu',
            'excerpt' => 'Taman Nasional Lore Lindu menyimpan kekayaan flora dan fauna endemik Sulawesi yang luar biasa. Dari burung maleo hingga anoa, setiap spesies memiliki cerita unik yang menunggu untuk dijelajahi.',
            'content' => '<h2>Surga Biodiversitas di Jantung Sulawesi</h2>
<p>Taman Nasional Lore Lindu (TNLL) merupakan salah satu kawasan konservasi terpenting di Indonesia, terletak di Sulawesi Tengah dengan luas sekitar 217.991,18 hektar. Kawasan ini telah diakui UNESCO sebagai Cagar Biosfer pada tahun 1977, menegaskan nilai ekologisnya yang luar biasa bagi dunia.</p>

<h2>Flora Endemik yang Memukau</h2>
<p>Hutan hujan tropis TNLL didominasi oleh berbagai jenis tumbuhan khas Wallacea. Beberapa spesies pohon yang mendominasi antara lain:</p>
<ul>
<li><strong>Palaquium obtusifolium</strong> - Pohon yang menghasilkan getah damar</li>
<li><strong>Castanopsis acuminatissima</strong> - Kerabat pohon berangan</li>
<li><strong>Lithocarpus spp.</strong> - Jenis oak tropis</li>
<li><strong>Aneka jenis anggrek</strong> - Lebih dari 200 spesies anggrek ditemukan di kawasan ini</li>
</ul>
<p>Pada ketinggian di atas 2.000 meter, vegetasi berubah menjadi hutan lumut (mossy forest) yang mistis dengan pohon-pohon yang diselimuti lumut tebal.</p>

<h2>Fauna Ikonik Sulawesi</h2>
<p>TNLL menjadi rumah bagi banyak spesies fauna endemik Sulawesi yang tidak ditemukan di tempat lain di dunia:</p>

<h3>Mamalia</h3>
<ul>
<li><strong>Anoa (Bubalus depressicornis)</strong> - Kerbau kerdil endemik Sulawesi</li>
<li><strong>Babirusa (Babyrousa celebensis)</strong> - Babi hutan dengan taring unik melengkung ke atas</li>
<li><strong>Kuskus Sulawesi (Ailurops ursinus)</strong> - Marsupial yang aktif pada malam hari</li>
<li><strong>Tarsius Sulawesi (Tarsius tarsier)</strong> - Primata terkecil dengan mata besar</li>
</ul>

<h3>Burung</h3>
<ul>
<li><strong>Maleo (Macrocephalon maleo)</strong> - Burung megapoda yang meletakkan telur di pasir vulkanik</li>
<li><strong>Rangkong Sulawesi (Rhyticeros cassidix)</strong> - Burung enggang dengan tanduk khas</li>
<li><strong>Elang Sulawesi (Nisaetus lanceolatus)</strong> - Predator puncak di ekosistem hutan</li>
</ul>

<h2>Warisan Budaya Megalitik</h2>
<p>Selain kekayaan alam, TNLL juga menyimpan warisan budaya berupa patung-patung megalitik di Lembah Bada, Napu, dan Besoa. Patung-patung batu ini diperkirakan berasal dari 1.000 tahun yang lalu dan menambah daya tarik wisata kawasan ini.</p>

<h2>Pentingnya Konservasi</h2>
<p>Sebagai hotspot biodiversitas, TNLL menghadapi berbagai ancaman seperti perburuan liar, perambahan hutan, dan perubahan iklim. Upaya konservasi terus dilakukan melalui patroli rutin, edukasi masyarakat, dan program ekowisata berkelanjutan.</p>

<p>Dengan mengunjungi dan mengenal lebih dekat Taman Nasional Lore Lindu, kita turut berkontribusi dalam melestarikan warisan alam Indonesia untuk generasi mendatang.</p>',
            'category' => 'artikel',
            'author_name' => 'Tim Edukasi TNLL',
            'is_published' => true,
            'is_featured' => true,
            'published_at' => now(),
            'views_count' => 0,
            'likes_count' => 0,
            'meta_title' => 'Keanekaragaman Hayati Taman Nasional Lore Lindu | TNLL',
            'meta_description' => 'Jelajahi kekayaan flora dan fauna endemik Sulawesi di Taman Nasional Lore Lindu. Dari anoa hingga maleo, temukan keajaiban biodiversitas Indonesia.',
            'meta_keywords' => 'taman nasional lore lindu, flora fauna sulawesi, anoa, maleo, konservasi, ekowisata',
            'locale' => 'id',
        ]);

        $this->command->info('Sample article created: Mengenal Keanekaragaman Hayati Taman Nasional Lore Lindu');
    }
}
