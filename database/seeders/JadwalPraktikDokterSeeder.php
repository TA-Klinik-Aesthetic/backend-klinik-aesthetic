<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JadwalPraktikDokterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_jadwal_praktik_dokter')->insert([
            [
                'id_dokter' => 1, // ID dokter sesuai dengan data di tabel tb_dokter
                'tgl_kerja' => '2025-05-01',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '12:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_dokter' => 1, // ID dokter sesuai dengan data di tabel tb_dokter
                'tgl_kerja' => '2025-05-02',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '12:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_dokter' => 2, // ID dokter sesuai dengan data di tabel tb_dokter
                'tgl_kerja' => '2025-05-03',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '12:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_dokter' => 2, // ID dokter sesuai dengan data di tabel tb_dokter
                'tgl_kerja' => '2025-05-04',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '12:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_dokter' => 3, // ID dokter sesuai dengan data di tabel tb_dokter
                'tgl_kerja' => '2025-05-05',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '12:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_dokter' => 3, // ID dokter sesuai dengan data di tabel tb_dokter
                'tgl_kerja' => '2025-05-06',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '12:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
