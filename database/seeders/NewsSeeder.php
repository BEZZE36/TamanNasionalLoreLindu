<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $newsItems = [
            [
                'title' => 'Program Konservasi Anoa TNLL Berhasil Tingkatkan Populasi 30%',
                'excerpt' => 'Tim konservasi Taman Nasional Lore Lindu berhasil meningkatkan populasi Anoa sebesar 30% melalui program perlindungan habitat dan monitoring intensif selama 2 tahun terakhir.',
                'content' => '<p>Tim konservasi <strong>Taman Nasional Lore Lindu (TNLL)</strong> mengumumkan keberhasilan besar dalam upaya pelestarian Anoa, mamalia endemik Sulawesi yang terancam punah.</p>
<p>Program konservasi yang telah berjalan selama dua tahun ini menunjukkan hasil yang menggembirakan dengan peningkatan populasi Anoa sebesar 30% dari jumlah sebelumnya.</p>
<h3>Strategi Konservasi</h3>
<ul>
<li>Monitoring habitat secara intensif menggunakan teknologi kamera trap</li>
<li>Patroli rutin untuk mencegah perburuan liar</li>
<li>Edukasi kepada masyarakat sekitar tentang pentingnya konservasi</li>
<li>Rehabilitasi habitat alami Anoa</li>
</ul>
<p>Kepala Balai TNLL menyatakan bahwa keberhasilan ini tidak terlepas dari dukungan masyarakat lokal dan kerjasama dengan berbagai pihak.</p>',
                'is_featured' => true,
            ],
            [
                'title' => 'Festival Budaya Lore Lindu 2024 Sukses Digelar dengan 5000 Pengunjung',
                'excerpt' => 'Festival tahunan yang menampilkan kekayaan budaya masyarakat adat di sekitar TNLL berhasil menarik ribuan wisatawan dari berbagai daerah.',
                'content' => '<p><strong>Festival Budaya Lore Lindu 2024</strong> telah sukses diselenggarakan dengan antusiasme yang luar biasa dari masyarakat dan wisatawan.</p>
<p>Festival yang berlangsung selama 3 hari ini menampilkan berbagai pertunjukan seni tradisional, pameran kerajinan tangan, dan kuliner khas daerah.</p>
<h3>Highlight Acara</h3>
<ul>
<li>Tarian tradisional Kaili dan Kulawi</li>
<li>Pameran megalitik replika Lembah Bada</li>
<li>Workshop tenun tradisional</li>
<li>Lomba foto alam TNLL</li>
</ul>
<p>Panitia berharap festival ini dapat terus menjadi ajang promosi wisata dan pelestarian budaya lokal.</p>',
                'is_featured' => true,
            ],
            [
                'title' => 'TNLL Luncurkan Sistem Tiket Online untuk Kemudahan Wisatawan',
                'excerpt' => 'Sistem e-ticketing terbaru memungkinkan wisatawan memesan tiket masuk secara online dan menghindari antrian panjang di loket.',
                'content' => '<p>Dalam upaya meningkatkan pelayanan kepada wisatawan, <strong>Taman Nasional Lore Lindu</strong> resmi meluncurkan sistem pemesanan tiket online.</p>
<p>Sistem baru ini dapat diakses melalui website resmi dan memungkinkan wisatawan untuk:</p>
<ul>
<li>Memesan tiket masuk dari rumah</li>
<li>Memilih tanggal kunjungan yang diinginkan</li>
<li>Melakukan pembayaran digital</li>
<li>Menerima e-ticket via email</li>
</ul>
<h3>Keuntungan Sistem Baru</h3>
<p>Dengan sistem ini, wisatawan tidak perlu lagi mengantri panjang di loket dan dapat langsung check-in dengan menunjukkan QR code pada tiket elektronik.</p>
<p>Pembayaran dapat dilakukan melalui berbagai metode termasuk transfer bank, e-wallet, dan kartu kredit.</p>',
                'is_featured' => false,
            ],
            [
                'title' => 'Penemuan Spesies Burung Langka di Kawasan Danau Tambing',
                'excerpt' => 'Tim peneliti dari LIPI menemukan spesies burung langka yang belum pernah tercatat sebelumnya di kawasan Danau Tambing, TNLL.',
                'content' => '<p>Sebuah penemuan mengejutkan terjadi di kawasan <strong>Danau Tambing</strong>, salah satu destinasi wisata populer di TNLL.</p>
<p>Tim peneliti dari Lembaga Ilmu Pengetahuan Indonesia (LIPI) berhasil mendokumentasikan keberadaan spesies burung langka yang belum pernah tercatat dalam database keanekaragaman hayati Indonesia.</p>
<h3>Detail Penemuan</h3>
<p>Burung yang ditemukan memiliki karakteristik unik dengan bulu berwarna hijau kebiruan dan paruh melengkung khas. Penemuan ini menambah daftar panjang kekayaan fauna endemik di TNLL.</p>
<p>Penelitian lebih lanjut akan dilakukan untuk mengklasifikasikan spesies baru ini dan menentukan status konservasinya.</p>',
                'is_featured' => false,
            ],
            [
                'title' => 'Pelatihan Pemandu Wisata Lokal untuk Tingkatkan Kualitas Layanan',
                'excerpt' => 'Sebanyak 50 pemandu wisata lokal mengikuti pelatihan intensif untuk meningkatkan pengetahuan dan kemampuan dalam melayani wisatawan.',
                'content' => '<p>Balai Taman Nasional Lore Lindu menggelar <strong>pelatihan pemandu wisata</strong> bagi masyarakat lokal yang berminat menjadi guide profesional.</p>
<p>Pelatihan yang berlangsung selama satu minggu ini diikuti oleh 50 peserta dari berbagai desa di sekitar TNLL.</p>
<h3>Materi Pelatihan</h3>
<ul>
<li>Pengenalan flora dan fauna TNLL</li>
<li>Teknik komunikasi dan hospitality</li>
<li>Keselamatan dan pertolongan pertama di alam</li>
<li>Bahasa Inggris dasar untuk pariwisata</li>
<li>Pengetahuan sejarah dan budaya lokal</li>
</ul>
<p>Para peserta yang lulus akan mendapatkan sertifikat resmi dan kesempatan untuk bergabung dengan asosiasi pemandu wisata TNLL.</p>',
                'is_featured' => false,
            ],
        ];

        foreach ($newsItems as $index => $news) {
            Article::create([
                'title' => $news['title'],
                'slug' => Str::slug($news['title']),
                'excerpt' => $news['excerpt'],
                'content' => $news['content'],
                'category' => 'berita',
                'author_name' => 'Admin TNLL',
                'is_published' => true,
                'is_featured' => $news['is_featured'],
                'published_at' => now()->subDays(rand(1, 30)),
                'views_count' => rand(50, 500),
            ]);
        }
    }
}
