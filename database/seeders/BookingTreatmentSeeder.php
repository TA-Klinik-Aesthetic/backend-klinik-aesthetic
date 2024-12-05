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
                'status_booking_treatment' => 'Berhasil dibooking',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id_user' => 2,
                'waktu_treatment' => now()->addDay(),
                'status_booking_treatment' => 'Dibatalkan',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
