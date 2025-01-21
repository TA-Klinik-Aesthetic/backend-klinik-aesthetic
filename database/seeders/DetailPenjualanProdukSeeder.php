<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetailPenjualanProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_detail_penjualan_produk')->insert([
            [
                'id_penjualan_produk' => 1,
                'id_produk' => 1,
                'jumlah_produk' => 2,
                'harga_penjualan_produk' => 50000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_penjualan_produk' => 1,
                'id_produk' => 2,
                'jumlah_produk' => 1,
                'harga_penjualan_produk' => 50000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_penjualan_produk' => 2,
                'id_produk' => 3,
                'jumlah_produk' => 3,
                'harga_penjualan_produk' => 25000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
