<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersSeeder::class,

            DokterSeeder::class,
            JadwalPraktikDokterSeeder::class,
            KonsultasiSeeder::class,
            DetailKonsultasiSeeder::class,
            FeedbackKonsultasiSeeder::class,

            BeauticianSeeder::class,
            JadwalPraktikBeauticianSeeder::class,
            JenisTreatmentSeeder::class,
            TreatmentSeeder::class,
            BookingTreatmentSeeder::class,
            DetailBookingTreatmentSeeder::class
        ]);
    }
}
