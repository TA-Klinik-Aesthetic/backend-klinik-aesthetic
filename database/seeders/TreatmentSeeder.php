<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TreatmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_treatment')->insert([
            [
                'id_jenis_treatment' => 1,
                'nama_treatment' => 'Ultimate Glow Skin',
                'deskripsi_treatment' => 'Facial intensif yang menenangkan dan mencerahkan kulit sensitif dengan formula lembut berbahan alami.',
                'biaya_treatment' => 400000.00,
                'estimasi_treatment' => '01:00:00',
                'gambar_treatment' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_jenis_treatment' => 1,
                'nama_treatment' => 'Baby Peel',
                'deskripsi_treatment' => 'Perawatan pengelupasan ringan untuk mengangkat sel kulit mati dan membuat kulit tampak halus dan bercahaya.',
                'biaya_treatment' => 300000.00,
                'estimasi_treatment' => '01:30:00',
                'gambar_treatment' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_jenis_treatment' => 2,
                'nama_treatment' => 'Under Arm Glow',
                'deskripsi_treatment' => 'Perawatan pemutih ketiak yang menutrisi dan meratakan warna kulit, menjadikannya lembut dan cerah.',
                'biaya_treatment' => 300000.00,
                'estimasi_treatment' => '00:30:00',
                'gambar_treatment' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_jenis_treatment' => 3,
                'nama_treatment' => 'Eye Bag RF',
                'deskripsi_treatment' => 'Teknologi radiofrekuensi untuk mengencangkan area bawah mata, mengurangi kantung dan garis halus.',
                'biaya_treatment' => 200000.00,
                'estimasi_treatment' => '01:00:00',
                'gambar_treatment' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_jenis_treatment' => 4,
                'nama_treatment' => 'Pink Lip Laser',
                'deskripsi_treatment' => 'Terapi laser lembut untuk mencerahkan dan menghaluskan bibir, menciptakan rona pink alami.',
                'biaya_treatment' => 500000.00,
                'estimasi_treatment' => '01:30:00',
                'gambar_treatment' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_jenis_treatment' => 5,
                'nama_treatment' => 'Vitamin C Booster Injection',
                'deskripsi_treatment' => 'Injeksi vitamin C berkonsentrasi tinggi untuk meningkatkan kecerahan dan elastisitas kulit wajah.',
                'biaya_treatment' => 200000.00,
                'estimasi_treatment' => '00:30:00',
                'gambar_treatment' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_jenis_treatment'  => 6,
                'nama_treatment'      => 'Tattoo Laser',
                'deskripsi_treatment' => 'Laser canggih untuk menghapus tato dengan cepat dan minim rasa sakit.',
                'biaya_treatment'     => 600000.00,
                'estimasi_treatment'  => '01:00:00',
                'gambar_treatment'    => '',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'id_jenis_treatment'  => 6,
                'nama_treatment'      => 'Face Toning Laser',
                'deskripsi_treatment' => 'Terapi laser untuk mengencangkan dan meratakan tekstur kulit wajah secara klinis.',
                'biaya_treatment'     => 650000.00,
                'estimasi_treatment'  => '01:15:00',
                'gambar_treatment'    => '',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
        ]);
    }
}
