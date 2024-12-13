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
                'deskripsi_treatment' => 'Facial untuk kulit sensitif',
                'biaya_treatment' => 400000.00,
                'estimasi_treatment' => '01:00:00',
                'gambar_treatment' => 'https://zapclinic.com/storage/2024/02/1708676945.All_Face_Micro_Injection_treatment.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_jenis_treatment' => 1,
                'nama_treatment' => 'Baby Peel',
                'deskripsi_treatment' => 'Hair spa dengan minyak alami',
                'biaya_treatment' => 300000.00,
                'estimasi_treatment' => '01:30:00',
                'gambar_treatment' => 'https://zapclinic.com/storage/2024/04/1713757766.Chemical_Peeling_Acne_-_Glow(2).jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_jenis_treatment' => 2,
                'nama_treatment' => 'Under Arm Glow',
                'deskripsi_treatment' => 'perawatan kecantikan yang ditujukan untuk mencerahkan dan merawat kulit di area ketiak agar tampak lebih halus dan lebih cerah.',
                'biaya_treatment' => 300000.00,
                'estimasi_treatment' => '00:30:00',
                'gambar_treatment' => 'https://derma-express.com/assets/admin/assets/media/derma_treatment/500/Treatment_20220802072849.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_jenis_treatment' => 3,
                'nama_treatment' => 'Eye Bag RF',
                'deskripsi_treatment' => 'Perawatan kulit yang menggunakan teknologi gelombang radiofrekuensi untuk mengatasi masalah kantung mata (eye bags).',
                'biaya_treatment' => 200000.00,
                'estimasi_treatment' => '01:00:00',
                'gambar_treatment' => 'https://zapclinic.com/storage/2024/03/1710129737.461_x_486Eye_Peel.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_jenis_treatment' => 4,
                'nama_treatment' => 'Pink Lip Laser',
                'deskripsi_treatment' => 'Perawatan kulit yang menggunakan teknologi gelombang radiofrekuensi untuk mengatasi masalah kantung mata (eye bags).',
                'biaya_treatment' => 500000.00,
                'estimasi_treatment' => '01:30:00',
                'gambar_treatment' => 'https://www.theclinicindonesia.com/storages/2a018584-1086-11ec-b336-4dbe2edde1a0.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_jenis_treatment' => 4,
                'nama_treatment' => 'Vitamin C Booster Injection',
                'deskripsi_treatment' => 'Perawatan kulit yang menggunakan teknologi gelombang radiofrekuensi untuk mengatasi masalah kantung mata (eye bags).',
                'biaya_treatment' => 200000.00,
                'estimasi_treatment' => '00:30:00',
                'gambar_treatment' => 'https://derma-express.com/assets/admin/assets/media/derma_treatment/500/Treatment_20240902084925.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
