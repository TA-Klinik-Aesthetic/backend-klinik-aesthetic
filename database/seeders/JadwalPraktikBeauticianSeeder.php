<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JadwalPraktikBeauticianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_jadwal_praktik_beautician')->insert([
            [
                'id_beautician' => 1, // ID dokter sesuai dengan data di tabel tb_dokter
                'hari' => 'selasa',
                'tgl_kerja' => '2024-11-25',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '12:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
