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
                'estimasi_treatment' => '1 Jam',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_jenis_treatment' => 2,
                'nama_treatment' => 'Hair Spa',
                'deskripsi_treatment' => 'Hair spa dengan minyak alami',
                'biaya_treatment' => 200000.00,
                'estimasi_treatment' => '1 Jam 30 Menit',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
