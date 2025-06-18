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
                'waktu_konsultasi' => now(),
                'keluhan_pelanggan' => 'Saya ingin konsultasi karena banyak komedo di hidung dan Kulit muka terasa kasar',
                'status_booking_konsultasi' => 'Selesai',
                // 'pemeriksaan_fisik' => 'Kulit wajah: pori-pori terlihat besar di hidung; beberapa komedo tertutup di area hidung; tekstur wajah terasa kasar; tidak ada eritema signifikan; Tekanan Darah (TD) 116/74 mmHg; nadi 76 bpm',
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Tambahkan data dummy lainnya jika diperlukan
        ]);
    }
}
