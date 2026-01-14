<?php

namespace Database\Seeders;

use App\Models\Flora;
use Illuminate\Database\Seeder;

class FloraSeeder extends Seeder
{
    public function run(): void
    {
        $floraData = [
            [
                'name' => 'Anggrek Hitam',
                'local_name' => 'Anggrek Sulawesi',
                'scientific_name' => 'Coelogyne pandurata',
                'description' => 'Anggrek langka endemik Sulawesi dengan kelopak berwarna hijau kekuningan dan lidah hitam. Ditemukan di hutan pegunungan TNLL.',
                'image_path' => 'flora/fl1.jpg',
                'category' => 'endemik',
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Kantong Semar',
                'local_name' => 'Tumbuhan Karnivora',
                'scientific_name' => 'Nepenthes maxima',
                'description' => 'Tumbuhan karnivora yang menangkap serangga dengan kantong berbentuk periuk. Banyak ditemukan di daerah lembab.',
                'image_path' => 'flora/fl2.jpg',
                'category' => 'langka',
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Raflesia',
                'local_name' => 'Bunga Bangkai',
                'scientific_name' => 'Rafflesia arnoldii',
                'description' => 'Bunga terbesar di dunia yang hanya mekar beberapa hari. Sangat langka dan dilindungi.',
                'image_path' => 'flora/fl3.jpg',
                'category' => 'langka',
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Paku Tiang',
                'local_name' => 'Paku Pohon',
                'scientific_name' => 'Cyathea contaminans',
                'description' => 'Paku raksasa yang tumbuh di hutan hujan tropis pegunungan. Dapat mencapai tinggi 15 meter.',
                'image_path' => 'flora/fl4.jpg',
                'category' => 'umum',
                'is_featured' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Cemara Gunung',
                'local_name' => 'Cemara Sulawesi',
                'scientific_name' => 'Casuarina junghuhniana',
                'description' => 'Pohon cemara yang tumbuh di ketinggian 1500-2500 mdpl. Pohon ikonik di sekitar Danau Tambing.',
                'image_path' => 'flora/fl5.jpg',
                'category' => 'umum',
                'is_featured' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Rotan Lore',
                'local_name' => 'Uwi',
                'scientific_name' => 'Calamus sp.',
                'description' => 'Jenis rotan endemik yang banyak digunakan masyarakat lokal untuk kerajinan tradisional.',
                'image_path' => 'flora/fl6.jpg',
                'category' => 'endemik',
                'is_featured' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Pakis Haji',
                'local_name' => 'Pakis Raja',
                'scientific_name' => 'Cycas rumphii',
                'description' => 'Tumbuhan purba yang masih bertahan hingga kini. Fosil hidup yang dilindungi.',
                'image_path' => 'flora/fl7.jpg',
                'category' => 'langka',
                'is_featured' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Bambu Hitam',
                'local_name' => 'Bambu Petung',
                'scientific_name' => 'Gigantochloa atroviolacea',
                'description' => 'Bambu dengan batang berwarna ungu kehitaman. Digunakan untuk konstruksi dan kerajinan.',
                'image_path' => 'flora/fl8.jpg',
                'category' => 'umum',
                'is_featured' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Kayu Eboni',
                'local_name' => 'Kayu Hitam Sulawesi',
                'scientific_name' => 'Diospyros celebica',
                'description' => 'Kayu mewah endemik Sulawesi dengan warna hitam pekat. Sangat langka dan dilindungi.',
                'image_path' => 'flora/fl9.jpg',
                'category' => 'endemik',
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Lumut Tanduk',
                'local_name' => 'Lumut Hutan',
                'scientific_name' => 'Anthoceros sp.',
                'description' => 'Lumut unik yang tumbuh di batang pohon hutan hujan. Indikator kualitas udara yang baik.',
                'image_path' => 'flora/fl10.jpg',
                'category' => 'umum',
                'is_featured' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Bunga Edelweis Jawa',
                'local_name' => 'Bunga Abadi',
                'scientific_name' => 'Anaphalis javanica',
                'description' => 'Bunga ikonik pegunungan tinggi yang tidak layu. Simbol keabadian dan cinta sejati.',
                'image_path' => 'flora/fl11.jpg',
                'category' => 'langka',
                'is_featured' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Pinus Merkusii',
                'local_name' => 'Tusam',
                'scientific_name' => 'Pinus merkusii',
                'description' => 'Satu-satunya pinus tropis asli Indonesia. Tumbuh di ketinggian 800-2000 mdpl.',
                'image_path' => 'flora/fl12.jpg',
                'category' => 'umum',
                'is_featured' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Daun Sang',
                'local_name' => 'Pohon Payung',
                'scientific_name' => 'Johannesteijsmannia altifrons',
                'description' => 'Palem dengan daun raksasa berbentuk kipas. Daun dapat mencapai lebar 6 meter.',
                'image_path' => 'flora/fl13.jpg',
                'category' => 'langka',
                'is_featured' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Wijaya Kusuma',
                'local_name' => 'Bunga Malam',
                'scientific_name' => 'Epiphyllum oxypetalum',
                'description' => 'Kaktus epifit yang berbunga di malam hari. Bunga wangi dan hanya mekar semalam.',
                'image_path' => 'flora/fl14.jpg',
                'category' => 'umum',
                'is_featured' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Jelutung',
                'local_name' => 'Pohon Getah',
                'scientific_name' => 'Dyera costulata',
                'description' => 'Pohon penghasil getah untuk industri. Tumbuh menjulang tinggi di hutan primer.',
                'image_path' => 'flora/fl15.jpg',
                'category' => 'umum',
                'is_featured' => false,
                'is_active' => true,
            ],
        ];

        foreach ($floraData as $index => $data) {
            $data['sort_order'] = $index;
            Flora::create($data);
        }
    }
}
