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
            [
                'id_user' => 1, 
                'waktu_treatment' => now(),
                'status_booking_treatment' => 'Verifikasi', 
                'harga_total' => 550000,
                'id_promo' => 1,
                'potongan_harga' => 50000, 
                'harga_akhir_treatment' => 500000, 
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
