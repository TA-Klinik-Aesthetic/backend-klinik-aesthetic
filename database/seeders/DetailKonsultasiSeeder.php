<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class DetailKonsultasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_detail_konsultasi')->insert([
            [
                'id_konsultasi' => 1,
                'keluhan_pelanggan' => 'Sakit kepala dan demam',
                'saran_tindakan' => 'Istirahat cukup dan minum obat pereda nyeri',
                'id_treatment' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Tambahkan data dummy lainnya jika diperlukan
        ]);
    }
}
