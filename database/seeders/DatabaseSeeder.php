<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\DetailBookingProduk;
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

            JenisTreatmentSeeder::class,
            KategoriSeeder::class,
            ProdukSeeder::class,

            TreatmentSeeder::class,

            KonsultasiSeeder::class,
            DetailKonsultasiSeeder::class,
            FeedbackKonsultasiSeeder::class,

            PromoSeeder::class,
            KeranjangPembelianSeeder::class,
            BeauticianSeeder::class,
            BookingTreatmentSeeder::class,
            DetailBookingTreatmentSeeder::class,
            // DetailBookingProdukSeeder::class,
            FeedbackTreatmentSeeder::class,

            PenjualanProdukSeeder::class,
            DetailPenjualanProdukSeeder::class,

            KompensasiSeeder::class
        ]);
    }
}
