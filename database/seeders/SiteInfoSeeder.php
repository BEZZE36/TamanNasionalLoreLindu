<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SiteInfoSeeder extends Seeder
{
    public function run(): void
    {
        // Privacy Policy Content
        $privacyContent = '<h2>1. Pendahuluan</h2>
<p>TNLL Explore ("kami", "kita", atau "Platform") berkomitmen untuk melindungi privasi pengunjung dan pengguna layanan kami. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, menyimpan, dan melindungi informasi pribadi Anda ketika Anda menggunakan platform e-ticketing wisata Taman Nasional Lore Lindu.</p>

<h2>2. Informasi yang Kami Kumpulkan</h2>
<p>Kami mengumpulkan beberapa jenis informasi untuk menyediakan dan meningkatkan layanan kami:</p>
<ul>
<li><strong>Informasi Identitas:</strong> Nama lengkap, alamat email, nomor telepon, dan kewarganegaraan.</li>
<li><strong>Informasi Pemesanan:</strong> Detail pemesanan tiket, tanggal kunjungan, jumlah pengunjung, dan preferensi layanan.</li>
<li><strong>Informasi Pembayaran:</strong> Data transaksi pembayaran (diproses secara aman oleh payment gateway pihak ketiga).</li>
<li><strong>Informasi Teknis:</strong> Alamat IP, jenis browser, perangkat, dan data penggunaan website.</li>
</ul>

<h2>3. Penggunaan Informasi</h2>
<p>Informasi yang kami kumpulkan digunakan untuk:</p>
<ul>
<li>Memproses dan mengkonfirmasi pemesanan tiket Anda.</li>
<li>Mengirimkan tiket digital dan informasi terkait kunjungan.</li>
<li>Memberikan layanan pelanggan dan dukungan teknis.</li>
<li>Meningkatkan kualitas layanan dan pengalaman pengguna.</li>
</ul>

<h2>4. Keamanan Data</h2>
<p>Kami menerapkan langkah-langkah keamanan teknis dan organisasi yang sesuai untuk melindungi data pribadi Anda dari akses tidak sah, perubahan, pengungkapan, atau penghancuran. Ini termasuk enkripsi SSL, firewall, dan kontrol akses yang ketat.</p>

<h2>5. Hubungi Kami</h2>
<p>Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini, silakan hubungi kami melalui email atau telepon yang tersedia di halaman kontak.</p>';

        // Terms & Conditions Content
        $termsContent = '<h2>1. Penerimaan Syarat</h2>
<p>Dengan mengakses dan menggunakan layanan e-ticketing TNLL Explore, Anda menyetujui untuk terikat dengan Syarat dan Ketentuan ini. Jika Anda tidak menyetujui salah satu atau seluruh ketentuan ini, mohon untuk tidak menggunakan layanan kami.</p>

<h2>2. Layanan E-Ticketing</h2>
<p>Platform ini menyediakan layanan pemesanan tiket masuk secara online untuk Taman Nasional Lore Lindu. Tiket yang dibeli bersifat:</p>
<ul>
<li>Non-refundable (tidak dapat dikembalikan)</li>
<li>Hanya berlaku untuk tanggal yang dipilih</li>
<li>Tidak dapat dipindahtangankan</li>
</ul>

<h2>3. Proses Pemesanan</h2>
<ul>
<li>Pengunjung wajib memberikan informasi yang akurat dan lengkap saat pemesanan.</li>
<li>Konfirmasi pemesanan akan dikirim melalui email.</li>
<li>E-ticket harus ditunjukkan saat memasuki kawasan.</li>
</ul>

<h2>4. Pembayaran</h2>
<p>Pembayaran dapat dilakukan melalui berbagai metode yang tersedia termasuk transfer bank, e-wallet, dan kartu kredit. Pembayaran harus diselesaikan dalam waktu yang ditentukan.</p>

<h2>5. Pembatalan dan Perubahan</h2>
<p>Pembatalan atau perubahan jadwal kunjungan dapat dilakukan minimal 24 jam sebelum tanggal kunjungan dengan menghubungi customer service kami.</p>';

        // Contact Information
        $contactAddress = 'Jl. Prof. Dr. Yamin No. 17, Kelurahan Besusu Barat, Kecamatan Palu Timur, Kota Palu, Sulawesi Tengah 94111';
        $contactPhone = '(0451) 421 679';
        $contactWhatsapp = '+62 812 3456 7890';
        $contactEmail = 'info@tnll.go.id';
        $operationalHours = 'Senin - Jumat: 08:00 - 16:00 WIB\nSabtu: 08:00 - 12:00 WIB\nMinggu & Hari Libur: Tutup';
        $googleMapsEmbed = '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d127504.97089269!2d119.82956285!3d-0.8924096!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2d8be630c4a86c4d%3A0x3d39e6c3e534c41a!2sPalu%2C%20Palu%20City%2C%20Central%20Sulawesi!5e0!3m2!1sen!2sid!4v1703000000000!5m2!1sen!2sid" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>';

        // FAQ Items - Comprehensive data for all categories
        $faqItems = json_encode([
            // Tiket (5 items)
            ['category' => 'Tiket', 'question' => 'Bagaimana cara memesan tiket online?', 'answer' => 'Anda dapat memesan tiket melalui website TNLL Explore dengan langkah: 1) Pilih destinasi yang ingin dikunjungi, 2) Tentukan tanggal dan jumlah pengunjung, 3) Isi data diri lengkap, 4) Pilih metode pembayaran dan selesaikan transaksi. E-ticket akan dikirim ke email Anda dalam 5 menit setelah pembayaran berhasil.'],
            ['category' => 'Tiket', 'question' => 'Apakah tiket bisa di-refund atau dikembalikan?', 'answer' => 'Tiket yang sudah dibeli bersifat non-refundable (tidak dapat dikembalikan). Namun, perubahan jadwal kunjungan dapat dilakukan maksimal 24 jam sebelum tanggal kunjungan dengan menghubungi customer service kami melalui WhatsApp atau email.'],
            ['category' => 'Tiket', 'question' => 'Berapa lama masa berlaku tiket?', 'answer' => 'Tiket hanya berlaku untuk tanggal kunjungan yang dipilih saat pemesanan. Tiket tidak dapat digunakan di luar tanggal yang tertera dan tidak dapat diperpanjang. Pastikan Anda hadir sesuai jadwal yang dipilih.'],
            ['category' => 'Tiket', 'question' => 'Bagaimana cara menunjukkan e-ticket saat masuk?', 'answer' => 'Anda cukup menunjukkan e-ticket berupa QR Code yang dikirim ke email Anda. Petugas di lokasi akan memindai QR Code tersebut. Pastikan layar handphone Anda terlihat jelas atau Anda juga bisa mencetak e-ticket tersebut.'],
            ['category' => 'Tiket', 'question' => 'Apakah ada diskon untuk rombongan atau pelajar?', 'answer' => 'Ya, tersedia diskon khusus untuk: 1) Rombongan minimal 20 orang mendapat potongan 15%, 2) Pelajar/mahasiswa dengan menunjukkan kartu pelajar mendapat potongan 20%, 3) Anak-anak usia 3-12 tahun mendapat harga tiket anak. Hubungi kami untuk informasi lebih lanjut.'],

            // Wisata (5 items)
            ['category' => 'Wisata', 'question' => 'Apa saja aktivitas yang bisa dilakukan di TNLL?', 'answer' => 'Di Taman Nasional Lore Lindu Anda dapat melakukan berbagai aktivitas seperti: trekking/hiking, bird watching (pengamatan burung endemik), mengunjungi situs megalitik di Lembah Bada, Besoa dan Napu, camping di area yang disediakan, berenang di Danau Tambing, serta menikmati keindahan air terjun dan flora fauna khas Sulawesi.'],
            ['category' => 'Wisata', 'question' => 'Kapan waktu terbaik untuk berkunjung?', 'answer' => 'Waktu terbaik untuk mengunjungi TNLL adalah pada musim kemarau antara bulan April hingga Oktober. Pada periode ini, jalur trekking lebih mudah dilalui dan cuaca lebih bersahabat. Namun, TNLL tetap bisa dikunjungi sepanjang tahun dengan persiapan yang memadai.'],
            ['category' => 'Wisata', 'question' => 'Apakah tersedia pemandu wisata lokal?', 'answer' => 'Ya, tersedia pemandu wisata lokal yang berpengalaman dan berlisensi. Pemandu dapat membantu Anda menjelajahi kawasan dengan aman, menjelaskan tentang flora-fauna endemik, serta sejarah situs megalitik. Anda dapat memesan pemandu melalui website atau langsung di pos jaga.'],
            ['category' => 'Wisata', 'question' => 'Apa yang harus dibawa saat berkunjung?', 'answer' => 'Barang yang perlu dibawa: 1) Sepatu hiking yang nyaman, 2) Pakaian ganti dan jaket (suhu bisa dingin), 3) Jas hujan/ponco, 4) Obat-obatan pribadi dan P3K, 5) Snack dan air minum yang cukup, 6) Kamera dan power bank, 7) Tas ransel yang nyaman. Hindari membawa barang berlebihan.'],
            ['category' => 'Wisata', 'question' => 'Apakah aman membawa anak-anak?', 'answer' => 'TNLL aman untuk anak-anak dengan pengawasan orang tua. Beberapa area seperti Danau Tambing dan situs megalitik cocok untuk keluarga. Namun, untuk jalur trekking yang sulit, disarankan anak minimal berusia 10 tahun dan dalam kondisi fisik yang baik.'],

            // Fasilitas (4 items)
            ['category' => 'Fasilitas', 'question' => 'Fasilitas apa saja yang tersedia di kawasan TNLL?', 'answer' => 'Fasilitas yang tersedia meliputi: area parkir kendaraan, toilet/MCK, shelter dan gazebo untuk istirahat, camping ground dengan area api unggun, pos informasi dan jaga, warung/kantin di beberapa titik, serta pusat informasi dengan peta kawasan. Beberapa area juga memiliki penginapan sederhana.'],
            ['category' => 'Fasilitas', 'question' => 'Apakah ada penginapan di dalam kawasan?', 'answer' => 'Tersedia beberapa pilihan penginapan: 1) Camping ground untuk Anda yang membawa tenda sendiri, 2) Homestay warga lokal di desa-desa sekitar kawasan, 3) Guest house di beberapa pos kawasan. Untuk reservasi penginapan, hubungi kantor pengelola TNLL atau pesan melalui website.'],
            ['category' => 'Fasilitas', 'question' => 'Apakah kawasan ramah untuk penyandang disabilitas?', 'answer' => 'Beberapa area seperti pusat informasi dan area piknik sudah dilengkapi aksesibilitas untuk penyandang disabilitas. Namun, untuk jalur trekking dan area alam lainnya masih terbatas. Kami menyarankan untuk menghubungi kami terlebih dahulu agar dapat membantu mempersiapkan kunjungan Anda.'],
            ['category' => 'Fasilitas', 'question' => 'Apakah tersedia jaringan internet/sinyal di kawasan?', 'answer' => 'Sinyal telepon dan internet tersedia di beberapa area seperti pintu masuk dan pos jaga, namun sangat terbatas atau tidak ada di area pedalaman dan jalur trekking. Disarankan untuk mengunduh peta offline dan memberitahu orang terdekat tentang rencana perjalanan Anda.'],

            // Pembayaran (4 items)
            ['category' => 'Pembayaran', 'question' => 'Metode pembayaran apa saja yang tersedia?', 'answer' => 'TNLL Explore menerima berbagai metode pembayaran: 1) Transfer Bank (BCA, BNI, BRI, Mandiri, dan bank lainnya), 2) E-Wallet (GoPay, OVO, Dana, ShopeePay, LinkAja), 3) Kartu Kredit/Debit (Visa, Mastercard), 4) Virtual Account, 5) Minimarket (Alfamart, Indomaret).'],
            ['category' => 'Pembayaran', 'question' => 'Berapa batas waktu pembayaran?', 'answer' => 'Batas waktu pembayaran adalah 2 jam setelah pemesanan untuk e-wallet dan virtual account, atau 24 jam untuk transfer bank manual. Jika melewati batas waktu, pesanan akan otomatis dibatalkan dan Anda perlu melakukan pemesanan ulang.'],
            ['category' => 'Pembayaran', 'question' => 'Apakah pembayaran aman?', 'answer' => 'Ya, semua transaksi pembayaran di TNLL Explore diproses dengan aman menggunakan enkripsi SSL dan melalui payment gateway yang tersertifikasi PCI-DSS. Data kartu dan informasi pembayaran Anda tidak disimpan di server kami.'],
            ['category' => 'Pembayaran', 'question' => 'Bagaimana jika pembayaran gagal?', 'answer' => 'Jika pembayaran gagal, cek saldo/limit Anda, pastikan data yang dimasukkan benar, dan coba metode pembayaran lain. Jika dana sudah terpotong namun tidak mendapat konfirmasi, hubungi customer service kami dengan menyertakan bukti pembayaran untuk verifikasi manual.'],

            // Umum (4 items)
            ['category' => 'Umum', 'question' => 'Apa itu Taman Nasional Lore Lindu?', 'answer' => 'Taman Nasional Lore Lindu (TNLL) adalah kawasan konservasi seluas 217.991 hektar di Sulawesi Tengah yang ditetapkan UNESCO sebagai Cagar Biosfer. TNLL merupakan habitat satwa endemik langka seperti Anoa, Babirusa, dan Maleo, serta menyimpan ratusan situs megalitik misterius berusia ribuan tahun di Lembah Bada, Besoa, dan Napu.'],
            ['category' => 'Umum', 'question' => 'Bagaimana cara menghubungi customer service?', 'answer' => 'Anda dapat menghubungi customer service TNLL Explore melalui: 1) WhatsApp di +62 812 3456 7890 (respon cepat), 2) Email di info@tnll.go.id, 3) Telepon kantor di (0451) 421 679 (jam kerja), 4) Form kontak di halaman Contact pada website. Tim kami siap membantu 24/7.'],
            ['category' => 'Umum', 'question' => 'Jam operasional kawasan wisata?', 'answer' => 'Kawasan wisata TNLL buka setiap hari mulai pukul 07:00 - 17:00 WITA. Untuk kegiatan camping atau trekking overnight, Anda perlu mendaftar dan mendapat izin khusus dari pos jaga. Kantor administrasi buka Senin-Jumat pukul 08:00-16:00 dan Sabtu 08:00-12:00 WITA.'],
            ['category' => 'Umum', 'question' => 'Apakah ada aturan khusus yang harus dipatuhi?', 'answer' => 'Ya, pengunjung wajib: 1) Tidak membuang sampah sembarangan (bawa kembali sampah Anda), 2) Tidak memetik tanaman atau mengganggu satwa, 3) Tidak membuat api unggun di luar area yang ditentukan, 4) Tidak membawa senjata tajam atau minuman beralkohol, 5) Mengikuti instruksi pemandu dan petugas, 6) Menghormati adat istiadat masyarakat lokal.'],
        ]);

        // Social Media Links
        $facebookUrl = 'https://facebook.com/tamannasionallorelindu';
        $instagramUrl = 'https://instagram.com/tnlorelindu';
        $twitterUrl = 'https://twitter.com/tnlorelindu';
        $youtubeUrl = 'https://youtube.com/@tamannasionallorelindu';
        $tiktokUrl = '';

        // Insert all settings
        $settings = [
            // Pages Content
            ['key' => 'privacy_policy', 'value' => $privacyContent, 'type' => 'html', 'group' => 'pages', 'label' => 'Kebijakan Privasi', 'is_public' => true],
            ['key' => 'terms_conditions', 'value' => $termsContent, 'type' => 'html', 'group' => 'pages', 'label' => 'Syarat & Ketentuan', 'is_public' => true],

            // Contact Information
            ['key' => 'contact_address', 'value' => $contactAddress, 'type' => 'string', 'group' => 'contact', 'label' => 'Alamat', 'is_public' => true],
            ['key' => 'contact_phone', 'value' => $contactPhone, 'type' => 'string', 'group' => 'contact', 'label' => 'Telepon', 'is_public' => true],
            ['key' => 'contact_whatsapp', 'value' => $contactWhatsapp, 'type' => 'string', 'group' => 'contact', 'label' => 'WhatsApp', 'is_public' => true],
            ['key' => 'contact_email', 'value' => $contactEmail, 'type' => 'string', 'group' => 'contact', 'label' => 'Email', 'is_public' => true],
            ['key' => 'operational_hours', 'value' => $operationalHours, 'type' => 'string', 'group' => 'contact', 'label' => 'Jam Operasional', 'is_public' => true],
            ['key' => 'google_maps_embed', 'value' => $googleMapsEmbed, 'type' => 'html', 'group' => 'contact', 'label' => 'Google Maps Embed', 'is_public' => true],

            // FAQ
            ['key' => 'faq_items', 'value' => $faqItems, 'type' => 'json', 'group' => 'faq', 'label' => 'FAQ Items', 'is_public' => true],

            // Social Media
            ['key' => 'facebook_url', 'value' => $facebookUrl, 'type' => 'string', 'group' => 'social', 'label' => 'Facebook URL', 'is_public' => true],
            ['key' => 'instagram_url', 'value' => $instagramUrl, 'type' => 'string', 'group' => 'social', 'label' => 'Instagram URL', 'is_public' => true],
            ['key' => 'twitter_url', 'value' => $twitterUrl, 'type' => 'string', 'group' => 'social', 'label' => 'Twitter URL', 'is_public' => true],
            ['key' => 'youtube_url', 'value' => $youtubeUrl, 'type' => 'string', 'group' => 'social', 'label' => 'YouTube URL', 'is_public' => true],
            ['key' => 'tiktok_url', 'value' => $tiktokUrl, 'type' => 'string', 'group' => 'social', 'label' => 'TikTok URL', 'is_public' => true],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
