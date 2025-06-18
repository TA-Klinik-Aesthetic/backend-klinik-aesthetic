<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenjualanProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_penjualan_produk')->insert([
            [
                'id_user' => 1,
                'tanggal_pembelian' => now(),
                'harga_total' => 150000,
                'id_promo' => 1,
                'potongan_harga' => 50000,
                'besaran_pajak' => 10000,  
                'harga_akhir' => 110000,
                'status_pengambilan_produk' => 'Belum diambil',
                'waktu_pengambilan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_user' => 2,
                'tanggal_pembelian' => now(),
                'harga_total' => 75000,
                'id_promo' => 3,
                'potongan_harga' => 25,
                'besaran_pajak' => 5000,  
                'harga_akhir' => 55000,
                'status_pengambilan_produk' => 'Belum diambil',
                'waktu_pengambilan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
