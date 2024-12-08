<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetailBookingTreatmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Seeder untuk tb_detail_booking_treatment
         DB::table('tb_detail_booking_treatment')->insert([
            [
                'id_booking_treatment' => 1,
                'id_treatment' => 1,
                'harga_akhir_treatment' => 250000,
                'potongan_harga' => '10%',
                'id_dokter' => 1,
                'id_beautician' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id_booking_treatment' => 1,
                'id_treatment' => 2,
                'harga_akhir_treatment' => 300000,
                'potongan_harga' => '15%',
                'id_dokter' => 1,
                'id_beautician' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
