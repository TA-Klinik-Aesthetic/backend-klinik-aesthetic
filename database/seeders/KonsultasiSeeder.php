<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class KonsultasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_konsultasi')->insert([
            [
                'id_user' => 1, // Pastikan id_user ini sudah ada di tabel tb_user
                'id_dokter' => 1, // Pastikan id_dokter ini sudah ada di tabel tb_dokter
                'id_detail_konsultasi' => 1, // Pastikan id_detail_konsultasi ini sudah ada
                'id_feedback' => 4, // Pastikan id_feedback ini sudah ada di tabel tb_feedback
                'waktu_konsultasi' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Tambahkan data dummy lainnya jika diperlukan
        ]);
    }
}
