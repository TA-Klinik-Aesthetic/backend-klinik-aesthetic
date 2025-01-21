<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Promo;
use Illuminate\Support\Facades\DB;

class PromoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tb_promo')->insert([
            [
                'nama_promo' => 'Diskon Akhir Tahun',
                'deskripsi_promo' => 'Diskon hingga 50% untuk semua produk!',
                'potongan_harga' => 50000,
                'tanggal_mulai' => '2025-01-01',
                'tanggal_berakhir' => '2025-01-31',
                'gambar_promo' => 'promo_akhir_tahun.jpg',
                'status_promo' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_promo' => 'Promo Spesial Bulan Ini',
                'deskripsi_promo' => 'Dapatkan potongan harga Rp25.000 untuk pembelian minimal Rp100.000.',
                'potongan_harga' => 25000,
                'tanggal_mulai' => '2025-02-01',
                'tanggal_berakhir' => '2025-02-28',
                'gambar_promo' => 'promo_bulan_ini.jpg',
                'status_promo' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
