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
                'nama_treatment' => 'Facial wajah',
                'deskripsi_treatment' => 'Facial untuk kulit sensitif',
                'biaya_treatment' => 150000.00,
                'estimasi_treatment' => '01:00:00',
                'gambar_treatment' => 'https://static.theprint.in/wp-content/uploads/2023/07/Untitled-design-6-1.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_jenis_treatment' => 2,
                'nama_treatment' => 'Hair Spa',
                'deskripsi_treatment' => 'Hair spa dengan minyak alami',
                'biaya_treatment' => 200000.00,
                'estimasi_treatment' => '01:30:00',
                'gambar_treatment' => 'https://static.theprint.in/wp-content/uploads/2023/07/Untitled-design-6-1.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
