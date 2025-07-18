<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KompensasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_kompensasi')->insert([
            [
                'id_treatment'        => 1, // Ultimate Glow Skin
                'nama_kompensasi'     => 'Kompensasi Ultimate Glow',
                'deskripsi_kompensasi'=> 'Sebagai permohonan maaf karena hasil Ultimate Glow Skin belum memuaskan, pembebasan biaya 1 sesi.',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'id_treatment'        => 2, // Baby Peel
                'nama_kompensasi'     => 'Kompensasi Baby Peel',
                'deskripsi_kompensasi'=> 'Sebagai permohonan maaf karena hasil Baby Peel belum memuaskan, pembebasan biaya 1 sesi.',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'id_treatment'        => 3, // Under Arm Glow
                'nama_kompensasi'     => 'Kompensasi Under Arm Glow',
                'deskripsi_kompensasi'=> 'Sebagai permohonan maaf karena hasil Under Arm Glow belum memuaskan, pembebasan biaya 1 sesi.',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'id_treatment'        => 4, // Eye Bag RF
                'nama_kompensasi'     => 'Kompensasi Eye Bag RF',
                'deskripsi_kompensasi'=> 'Sebagai permohonan maaf karena hasil Eye Bag RF belum memuaskan, pembebasan biaya 1 sesi.',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'id_treatment'        => 5, // Pink Lip Laser
                'nama_kompensasi'     => 'Kompensasi Pink Lip Laser',
                'deskripsi_kompensasi'=> 'Sebagai permohonan maaf karena hasil Pink Lip Laser belum memuaskan, pembebasan biaya 1 sesi.',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'id_treatment'        => 6, // Vitamin C Booster Injection
                'nama_kompensasi'     => 'Kompensasi Vitamin C Booster',
                'deskripsi_kompensasi'=> 'Sebagai permohonan maaf karena hasil Vitamin C Booster Injection belum memuaskan, pembebasan biaya 1 sesi.',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
        ]);
    }
}
