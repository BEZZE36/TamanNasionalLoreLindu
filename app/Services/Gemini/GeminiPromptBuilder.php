<?php

namespace App\Services\Gemini;

class GeminiPromptBuilder
{
    /**
     * Build rewrite prompt with specified style
     */
    public function buildRewritePrompt(string $content, string $style): string
    {
        $styleInstructions = $this->getStyleInstructions($style);

        return "INSTRUKSI: {$styleInstructions}

TEKS ASLI:
{$content}

HASIL (tulis langsung hasilnya tanpa pengantar):";
    }

    /**
     * Get style instructions for rewriting
     */
    protected function getStyleInstructions(string $style): string
    {
        return match ($style) {
            'formal' => 'Peran: Editor Bahasa Tingkat Tinggi untuk publikasi resmi pemerintah/akademis.
TUGAS: Ubah teks ini menjadi SANGAT FORMAL, ELEGAN, dan BERWIBAWA.
PANDUAN:
- Gunakan kosa kata yang canggih (sophisticated) dan presisi.
- Struktur kalimat kompleks namun tetap jelas (syntactic complexity).
- Hilangkan semua unsur subjektif, kolokial, atau emosional berlebih.
- Nada: Objektif, Profesional, Respectful.
- Cocok untuk: Surat resmi, laporan tahunan, atau publikasi ilmiah.',

            'casual' => 'Peran: Sahabat akrab yang pandai bercerita (Storyteller).
TUGAS: Ubah teks ini menjadi SANGAT CAIR, HANGAT, dan MENYENANGKAN.
PANDUAN:
- Gunakan sapaan akrab jika memungkinkan (contoh: "Kawan", "Sobat Travel").
- Gunakan bahasa percakapan sehari-hari yang sopan.
- Tambahkan emotikon yang relevan secara natural 😊.
- Struktur kalimat pendek, dinamis, dan langsung.
- Nada: Antusias, Friendly, Mengundang.
- Cocok untuk: Caption Instagram, Blog pribadi, WhatsApp broadcast.',

            'seo' => 'Peran: Pakar SEO & Copywriting Digital Marketing Kelas Dunia.
TUGAS: Optimalkan teks ini untuk MESIN PENCARI (Google) namun tetap NYAMAN DIBACA manusia.
PANDUAN:
- Integrasikan LSI Keywords (Latent Semantic Indexing) secara natural.
- Gunakan "Power Words" di awal kalimat untuk meningkatkan CTR/engagement.
- Pecah paragraf panjang menjadi potongan "bite-sized" (maksimal 3-4 baris).
- Gunakan struktur Active Voice (Kalimat Aktif).
- Pertahankan keyword utama namun hindari keyword stuffing.
- Nada: Persuasif, Informatif, Action-oriented.',

            default => 'Perbaiki dan tingkatkan kualitas teks berikut agar lebih baik, bebas typo, dan lebih enak dibaca (flow yang lebih baik).',
        };
    }

    /**
     * Build summarize prompt
     */
    public function buildSummarizePrompt(string $content, int $maxSentences = 3): string
    {
        return "Kamu adalah editor profesional yang ahli dalam membuat ringkasan.

TUGAS: Ringkas teks berikut menjadi MAKSIMAL {$maxSentences} kalimat yang padat dan informatif.

PANDUAN:
- Tangkap esensi dan poin-poin terpenting
- Jangan hilangkan informasi kritis
- Gunakan bahasa yang jelas dan mudah dipahami
- Buat ringkasan yang bisa berdiri sendiri

TEKS UNTUK DIRINGKAS:
{$content}

RINGKASAN:";
    }

    /**
     * Build headlines prompt
     */
    public function buildHeadlinesPrompt(string $content, int $count = 5): string
    {
        return "Kamu adalah copywriter headline expert dengan pengalaman viral marketing.

TUGAS: Buatkan {$count} alternatif HEADLINE/JUDUL yang SANGAT MENARIK berdasarkan konten berikut.

KRITERIA HEADLINE YANG BAIK:
- Memicu rasa penasaran atau emosi
- Jelas dan spesifik
- Menggunakan power words
- Optimal 6-12 kata
- Clickable tapi tidak clickbait

KONTEN:
{$content}

FORMAT OUTPUT:
1. [Headline pertama]
2. [Headline kedua]
...dst

ALTERNATIF HEADLINE:";
    }

    /**
     * Build SEO suggestions prompt
     */
    public function buildSeoPrompt(string $content, ?string $targetKeyword = null): string
    {
        $keywordInfo = $targetKeyword ? "TARGET KEYWORD UTAMA: {$targetKeyword}\n\n" : "";

        return "Kamu adalah SEO Expert dan Content Strategist berpengalaman 10+ tahun.

TUGAS: Analisis konten berikut dan berikan REKOMENDASI SEO KOMPREHENSIF.

{$keywordInfo}KONTEN YANG DIANALISIS:
{$content}

BERIKAN ANALISIS DALAM FORMAT BERIKUT:

## 🎯 Kata Kunci yang Disarankan
[Sebutkan 8-10 kata kunci relevan, urutkan dari yang paling prioritas]

## 📝 Meta Description
[Tulis meta description yang menarik, maksimal 155 karakter]

## 📊 Meta Title
[Tulis meta title yang SEO-friendly, maksimal 60 karakter]

## 🏗️ Struktur Heading yang Disarankan
[Berikan saran struktur H1, H2, H3 yang optimal]

## ✍️ Saran Perbaikan Konten
[3-5 poin saran konkret untuk meningkatkan SEO konten]

## 📈 Readability Score
[Estimasi skor dan saran peningkatan]

ANALISIS SEO:";
    }

    /**
     * Build expand prompt
     */
    public function buildExpandPrompt(string $content): string
    {
        return "Kamu adalah penulis konten profesional yang ahli mengembangkan ide menjadi konten yang kaya dan informatif.

TUGAS: Kembangkan dan PERLUAS teks berikut menjadi konten yang LEBIH DETAIL, INFORMATIF, dan MENARIK.

PANDUAN PENGEMBANGAN:
- Tambahkan penjelasan yang lebih mendalam
- Sertakan contoh konkret atau ilustrasi jika relevan
- Tambahkan fakta menarik atau statistik (jika sesuai)
- Gunakan transisi yang smooth antar paragraf
- Pertahankan tone dan gaya penulisan asli
- Hasil harus 2-3x lebih panjang dari aslinya
- Format dengan paragraf yang rapi

TEKS ASLI:
{$content}

TEKS YANG DIKEMBANGKAN:";
    }

    /**
     * Build shorten prompt
     */
    public function buildShortenPrompt(string $content): string
    {
        return "Kamu adalah editor profesional yang ahli dalam penulisan ringkas dan efektif.

TUGAS: PERSINGKAT teks berikut menjadi lebih RINGKAS DAN PADAT tanpa kehilangan informasi penting.

PANDUAN:
- Hapus kata-kata yang tidak perlu (redundant words)
- Gabungkan kalimat yang bisa digabung
- Fokus pada poin-poin utama saja
- Hasil harus 40-60% dari panjang asli
- Tetap mudah dipahami dan mengalir
- Jangan hilangkan data/fakta penting

TEKS ASLI:
{$content}

TEKS RINGKAS:";
    }

    /**
     * Build generate content prompt
     */
    public function buildGeneratePrompt(string $topic, string $type = 'paragraph'): string
    {
        $typeInstructions = $this->getTypeInstructions($type);

        return "MODEL IDENTITAS: {$typeInstructions}

TOPIK UTAMA: {$topic}

INSTRUKSI TAMBAHAN:
- Gunakan Bahasa Indonesia yang baku, kaya kosa kata, dan sangat mengalir.
- Format output menggunakan Markdown yang rapi.
- Jangan berikan pengantar meta-komunikasi seperti 'Tentu, ini drafnya'. Langsung berikan hasil tulisan.

MULAI MENULIS SEKARANG:";
    }

    /**
     * Get type instructions for content generation
     */
    protected function getTypeInstructions(string $type): string
    {
        return match ($type) {
            'description' => "Kamu adalah Copywriter Elit kelas dunia yang mengkhususkan diri pada deskripsi pariwisata premium.
            
TUGAS: Buat deskripsi yang MEMUKAU, EKSOTIS, dan MENGGUGAH EMOSI tentang topik ini.
PANDUAN:
- Gunakan bahasa yang sangat deskriptif, puitis namun tetap informatif.
- Ajak pembaca berimajinasi seolah mereka berada di sana.
- Soroti keunikan, atmosfer, dan pengalaman sensory (suara, pemandangan, rasa).
- Gunakan kata-kata berkekuatan (power words) seperti 'mempesona', 'menakjubkan', 'surga tersembunyi'.
- Tutup dengan kalimat yang membuat mereka ingin segera berkunjung.
- Panjang: 150-300 kata yang padat makna.",

            'news' => "Kamu adalah Jurnalis Senior dari media berita terkemuka masa depan.
            
TUGAS: Tulis berita AKTUAL dan FAKTUAL tentang topik ini dengan gaya jurnalistik modern.
STRUKTUR BERITA:
1. **Headline**: Menarik, mendesak, dan informatif (H1).
2. **Dateline**: [Lokasi, Tanggal Hari Ini] - (Bold).
3. **Lead Paragraph**: 5W+1H (Who, What, Where, When, Why, How) dalam satu paragraf padat.
4. **Body**: Penjelasan detail, kutipan fiktif dari 'pengelola' atau 'ahli' untuk kredibilitas, dan konteks latar belakang.
5. **Impact**: Dampak bagi masyarakat atau pengunjung.
PANDUAN:
- Gunakan bahasa formal, objektif, dan lugas.
- Fokus pada kebaruan (novelty) dan relevansi.
- Gaya penulisan seperti CNN Indonesia atau Kompas.",

            'article' => "Kamu adalah Kontributor Ahli untuk majalah travel & lifestyle 'Future Nature'.
            
TUGAS: Tulis ARTIKEL MENDALAM (Feature Article) yang inspiratif dan berwawasan luas.
STRUKTUR ARTIKEL:
1. **Judul Memikat** (H1).
2. **Intro**: Anekdot singkat atau fakta mengejutkan sebagai 'hook'.
3. **Sub-bab Tematik** (Gunakan H2 dan H3): Bahas sejarah, keanekaragaman hayati, tips eksklusif, atau sudut pandang unik.
4. **Sisi Edukasi**: Masukkan fakta ilmiah atau budaya yang jarang diketahui (Did You Know?).
5. **Kesimpulan**: Refleksi filosofis singkat dan ajakan bertindak.
PANDUAN:
- Gaya bahasa: Bercerita (storytelling), elegan, dan berwawasan.
- Gunakan format markdown yang kaya (bold, italic, list).
- Minimal 600-800 kata.
- Optimasi SEO natural.",

            'paragraph' => "Tulis 3 paragraf padat yang informatif, menarik, dan langsung pada inti permasalahan topik ini.",

            default => "Tulis konten informatif dan menarik tentang topik ini.",
        };
    }

    /**
     * Build grammar improvement prompt
     */
    public function buildGrammarPrompt(string $content): string
    {
        return "Kamu adalah editor dan proofreader profesional untuk Bahasa Indonesia.

TUGAS: Perbaiki TATA BAHASA, EJAAN, dan KEJELASAN teks berikut.

YANG PERLU DIPERBAIKI:
- Kesalahan ejaan dan typo
- Kesalahan tata bahasa
- Kalimat yang ambigu atau tidak jelas
- Penggunaan tanda baca yang salah
- Konsistensi gaya penulisan

PENTING: Jangan ubah makna atau style penulisan secara signifikan.

TEKS ASLI:
{$content}

TEKS YANG DIPERBAIKI:";
    }

    /**
     * Build SEO tags generation prompt
     */
    public function buildSeoTagsPrompt(string $content): string
    {
        return "Kamu adalah Pakar SEO. Berdasarkan konten berikut, buatkan metadata SEO yang optimal.
        
KONTEN:
{$content}

TUGAS:
Kembalikan JSON object (tanpa markdown formatting seperti ```json) dengan key berikut:
- meta_title (max 60 karakter)
- meta_description (max 155 karakter, fun dan mengundang klik)
- keywords (8-10 keywords dipisahkan koma)

OUTPUT JSON ONLY:";
    }
}
