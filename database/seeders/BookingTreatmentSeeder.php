<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class BookingTreatmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_booking_treatment')->insert([
            // [
            //     'id_user' => 1, 
            //     'waktu_treatment' => '2025-07-10 10:00:00',
            //     'id_dokter' => 1, 
            //     'id_beautician' => 1, 
            //     'status_booking_treatment' => 'Verifikasi', 
            //     'harga_total' => 550000,
            //     'id_promo' => 2,
            //     'potongan_harga' => 25000,
            //     'besaran_pajak' => 5000,  
            //     'harga_akhir_treatment' => 550000, // hanya sekedar contoh
            //     'created_at' => now(),
            //     'updated_at' => now()
            // ],
        ]);
    }
}
