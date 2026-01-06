<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class LegalContentSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // KEBIJAKAN PRIVASI
        // ==========================================
        $privacyPolicy = <<<'HTML'
<h1>Kebijakan Privasi TNLL Explore</h1>

<p><strong>Terakhir diperbarui:</strong> Januari 2026</p>

<p>TNLL Explore ("kami", "kita", atau "Website") berkomitmen untuk melindungi privasi Anda. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, mengungkapkan, dan melindungi informasi Anda ketika Anda menggunakan layanan kami.</p>

<h2>1. Informasi yang Kami Kumpulkan</h2>

<h3>1.1 Informasi Pribadi</h3>
<p>Kami mengumpulkan informasi yang Anda berikan secara langsung kepada kami, termasuk:</p>
<ul>
<li><strong>Informasi Akun:</strong> Nama lengkap, alamat email, nomor telepon, dan kata sandi saat Anda mendaftar akun</li>
<li><strong>Informasi Pemesanan:</strong> Alamat, tanggal kunjungan, jumlah pengunjung, dan preferensi kunjungan</li>
<li><strong>Informasi Pembayaran:</strong> Detail pembayaran yang diperlukan untuk memproses transaksi (diproses melalui payment gateway yang aman)</li>
<li><strong>Komunikasi:</strong> Pesan yang Anda kirimkan kepada kami melalui formulir kontak atau email</li>
</ul>

<h3>1.2 Informasi yang Dikumpulkan Secara Otomatis</h3>
<p>Ketika Anda mengakses Website kami, kami secara otomatis mengumpulkan:</p>
<ul>
<li><strong>Data Log:</strong> Alamat IP, jenis browser, halaman yang dikunjungi, waktu akses, dan referring URL</li>
<li><strong>Informasi Perangkat:</strong> Jenis perangkat, sistem operasi, dan identifikasi unik perangkat</li>
<li><strong>Cookies:</strong> File kecil yang disimpan di perangkat Anda untuk meningkatkan pengalaman pengguna</li>
</ul>

<h2>2. Penggunaan Informasi</h2>

<p>Kami menggunakan informasi yang dikumpulkan untuk:</p>
<ul>
<li>Memproses dan mengelola pemesanan tiket kunjungan</li>
<li>Mengirimkan konfirmasi, notifikasi, dan informasi terkait layanan</li>
<li>Menanggapi pertanyaan, keluhan, dan permintaan dukungan</li>
<li>Mengirimkan newsletter dan informasi promosi (jika Anda berlangganan)</li>
<li>Menganalisis penggunaan Website untuk meningkatkan layanan</li>
<li>Mencegah penipuan dan menjaga keamanan platform</li>
<li>Mematuhi kewajiban hukum yang berlaku</li>
</ul>

<h2>3. Pembagian Informasi</h2>

<p>Kami <strong>tidak menjual</strong> informasi pribadi Anda kepada pihak ketiga. Kami hanya membagikan informasi Anda dalam situasi berikut:</p>
<ul>
<li><strong>Penyedia Layanan:</strong> Dengan pihak ketiga yang membantu kami mengoperasikan Website (payment gateway, layanan email, hosting)</li>
<li><strong>Persyaratan Hukum:</strong> Jika diwajibkan oleh hukum atau dalam menanggapi proses hukum yang sah</li>
<li><strong>Perlindungan Hak:</strong> Untuk melindungi hak, properti, atau keselamatan kami, pengguna kami, atau publik</li>
</ul>

<h2>4. Keamanan Data</h2>

<p>Kami menerapkan langkah-langkah keamanan yang ketat untuk melindungi informasi Anda:</p>
<ul>
<li>Enkripsi SSL/TLS untuk semua transfer data</li>
<li>Penyimpanan kata sandi dengan hash yang aman</li>
<li>Pembatasan akses ke data pribadi hanya untuk personel yang berwenang</li>
<li>Pemantauan keamanan secara berkala</li>
<li>Backup data reguler dengan enkripsi</li>
</ul>

<h2>5. Hak Anda</h2>

<p>Anda memiliki hak untuk:</p>
<ul>
<li><strong>Mengakses:</strong> Meminta salinan data pribadi yang kami simpan tentang Anda</li>
<li><strong>Memperbaiki:</strong> Memperbarui atau mengoreksi informasi yang tidak akurat</li>
<li><strong>Menghapus:</strong> Meminta penghapusan data pribadi Anda</li>
<li><strong>Membatasi:</strong> Meminta pembatasan pemrosesan data Anda</li>
<li><strong>Berhenti Berlangganan:</strong> Berhenti menerima komunikasi pemasaran kapan saja</li>
</ul>

<h2>6. Cookies</h2>

<p>Website kami menggunakan cookies untuk:</p>
<ul>
<li>Menyimpan preferensi pengguna</li>
<li>Menganalisis lalu lintas Website</li>
<li>Menyediakan fitur media sosial</li>
<li>Meningkatkan pengalaman pengguna secara keseluruhan</li>
</ul>

<p>Anda dapat mengatur browser Anda untuk menolak cookies, namun beberapa fitur Website mungkin tidak berfungsi dengan baik.</p>

<h2>7. Perubahan Kebijakan</h2>

<p>Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Perubahan signifikan akan diberitahukan melalui email atau pemberitahuan di Website. Kami mendorong Anda untuk meninjau kebijakan ini secara berkala.</p>

<h2>8. Hubungi Kami</h2>

<p>Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini atau ingin menggunakan hak Anda, silakan hubungi kami:</p>

<blockquote>
<strong>TNLL Explore</strong><br>
Email: privacy@tnll-explore.com<br>
Telepon: +62 xxx-xxxx-xxxx<br>
Alamat: Sulawesi Tengah, Indonesia
</blockquote>

<p><em>Dengan menggunakan layanan kami, Anda menyetujui praktik yang dijelaskan dalam Kebijakan Privasi ini.</em></p>
HTML;

        // ==========================================
        // SYARAT & KETENTUAN
        // ==========================================
        $termsConditions = <<<'HTML'
<h1>Syarat & Ketentuan TNLL Explore</h1>

<p><strong>Terakhir diperbarui:</strong> Januari 2026</p>

<p>Selamat datang di TNLL Explore! Syarat dan Ketentuan ini mengatur penggunaan Anda atas website dan layanan kami. Dengan mengakses atau menggunakan layanan kami, Anda menyetujui untuk terikat oleh syarat-syarat ini.</p>

<h2>1. Definisi</h2>

<ul>
<li><strong>"Website"</strong> merujuk pada TNLL Explore dan semua halaman terkait</li>
<li><strong>"Layanan"</strong> merujuk pada semua layanan yang disediakan melalui Website, termasuk pemesanan tiket dan informasi wisata</li>
<li><strong>"Pengguna"</strong> merujuk pada setiap individu yang mengakses atau menggunakan Website</li>
<li><strong>"Kami"</strong> merujuk pada pengelola TNLL Explore</li>
</ul>

<h2>2. Penggunaan Layanan</h2>

<h3>2.1 Kelayakan</h3>
<p>Untuk menggunakan layanan kami, Anda harus:</p>
<ul>
<li>Berusia minimal 17 tahun atau memiliki izin dari orang tua/wali</li>
<li>Memiliki kapasitas hukum untuk membuat perjanjian yang mengikat</li>
<li>Tidak dilarang untuk menggunakan layanan berdasarkan hukum yang berlaku</li>
</ul>

<h3>2.2 Akun Pengguna</h3>
<p>Jika Anda membuat akun, Anda bertanggung jawab untuk:</p>
<ul>
<li>Menjaga kerahasiaan kredensial akun Anda</li>
<li>Semua aktivitas yang terjadi di bawah akun Anda</li>
<li>Memberikan informasi yang akurat dan terkini</li>
<li>Segera memberitahu kami jika ada penggunaan tidak sah</li>
</ul>

<h2>3. Pemesanan dan Pembayaran</h2>

<h3>3.1 Proses Pemesanan</h3>
<ul>
<li>Semua pemesanan tunduk pada ketersediaan</li>
<li>Harga yang ditampilkan adalah harga final termasuk pajak yang berlaku</li>
<li>Konfirmasi pemesanan akan dikirim melalui email</li>
<li>E-ticket harus ditunjukkan saat memasuki lokasi wisata</li>
</ul>

<h3>3.2 Kebijakan Pembatalan</h3>
<ul>
<li><strong>7+ hari sebelum kunjungan:</strong> Pengembalian dana 100%</li>
<li><strong>3-6 hari sebelum kunjungan:</strong> Pengembalian dana 50%</li>
<li><strong>1-2 hari sebelum kunjungan:</strong> Pengembalian dana 25%</li>
<li><strong>Hari H kunjungan:</strong> Tidak ada pengembalian dana</li>
</ul>

<h3>3.3 Perubahan Jadwal</h3>
<p>Perubahan tanggal kunjungan dapat dilakukan maksimal 24 jam sebelum jadwal asli, tergantung ketersediaan. Biaya administrasi mungkin berlaku.</p>

<h2>4. Aturan Kunjungan</h2>

<p>Selama kunjungan ke destinasi wisata, pengunjung wajib:</p>
<ul>
<li>Mematuhi semua peraturan kawasan konservasi</li>
<li>Tidak merusak flora, fauna, atau fasilitas</li>
<li>Tidak membuang sampah sembarangan</li>
<li>Mengikuti petunjuk pemandu dan petugas</li>
<li>Menjaga ketenangan dan tidak mengganggu satwa liar</li>
<li>Tidak membawa senjata, alkohol, atau zat terlarang</li>
</ul>

<h2>5. Hak Kekayaan Intelektual</h2>

<p>Semua konten di Website, termasuk namun tidak terbatas pada:</p>
<ul>
<li>Teks, gambar, logo, dan desain grafis</li>
<li>Foto dan video</li>
<li>Kode program dan perangkat lunak</li>
</ul>

<p>Dilindungi oleh hak cipta dan tidak boleh digunakan tanpa izin tertulis dari kami.</p>

<h2>6. Batasan Tanggung Jawab</h2>

<p>Kami tidak bertanggung jawab atas:</p>
<ul>
<li>Kerugian yang timbul dari penggunaan atau ketidakmampuan menggunakan layanan</li>
<li>Kerusakan perangkat atau kehilangan data akibat mengakses Website</li>
<li>Kecelakaan atau cedera selama kunjungan (pengunjung wajib mengikuti prosedur keselamatan)</li>
<li>Perubahan kondisi cuaca atau bencana alam yang mempengaruhi kunjungan</li>
<li>Konten pihak ketiga yang terhubung dari Website kami</li>
</ul>

<h2>7. Ganti Rugi</h2>

<p>Anda setuju untuk mengganti rugi dan membebaskan kami dari segala klaim, kerugian, atau biaya yang timbul akibat:</p>
<ul>
<li>Pelanggaran Anda terhadap Syarat dan Ketentuan ini</li>
<li>Pelanggaran Anda terhadap hak pihak ketiga</li>
<li>Penggunaan Anda atas layanan kami yang melanggar hukum</li>
</ul>

<h2>8. Perubahan Syarat</h2>

<p>Kami berhak untuk mengubah Syarat dan Ketentuan ini kapan saja. Perubahan akan berlaku efektif segera setelah dipublikasikan di Website. Penggunaan berkelanjutan Anda atas layanan kami merupakan penerimaan terhadap syarat yang diubah.</p>

<h2>9. Hukum yang Berlaku</h2>

<p>Syarat dan Ketentuan ini diatur oleh dan ditafsirkan sesuai dengan hukum Republik Indonesia. Setiap sengketa akan diselesaikan melalui musyawarah, dan jika tidak tercapai kesepakatan, akan diselesaikan di pengadilan yang berwenang di Indonesia.</p>

<h2>10. Hubungi Kami</h2>

<p>Untuk pertanyaan tentang Syarat dan Ketentuan ini, silakan hubungi:</p>

<blockquote>
<strong>TNLL Explore</strong><br>
Email: legal@tnll-explore.com<br>
Telepon: +62 xxx-xxxx-xxxx<br>
Alamat: Sulawesi Tengah, Indonesia
</blockquote>

<p><em>Dengan menggunakan layanan TNLL Explore, Anda mengakui bahwa Anda telah membaca, memahami, dan menyetujui Syarat dan Ketentuan ini.</em></p>
HTML;

        // Save to database
        Setting::setSetting('privacy_policy', $privacyPolicy, 'html');
        Setting::setSetting('terms_conditions', $termsConditions, 'html');

        $this->command->info('✅ Privacy Policy and Terms & Conditions content seeded successfully!');
    }
}
