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
                'keluhan_pelanggan' => 'Sakit kepala dan demam',
                'diagnosa_dokter' => 'Migrain ringan',
                'saran_tindakan' => 'Istirahat cukup dan minum obat pereda nyeri',
                'resep_obat' => 'Paracetamol 500mg',
                'catatan_tambahan' => 'Periksa ulang jika gejala tidak membaik dalam 3 hari',
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Tambahkan data dummy lainnya jika diperlukan
        ]);
    }
}
