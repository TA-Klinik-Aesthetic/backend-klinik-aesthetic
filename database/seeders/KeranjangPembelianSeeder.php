<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KeranjangPembelianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_keranjang_pembelian')->insert([
            [
                'id_user' => 1,
                'id_produk' => 1,
                'jumlah' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_user' => 1,
                'id_produk' => 3,
                'jumlah' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_user' => 2,
                'id_produk' => 2,
                'jumlah' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_user' => 2,
                'id_produk' => 5,
                'jumlah' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_user' => 3,
                'id_produk' => 4,
                'jumlah' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_user' => 3,
                'id_produk' => 6,
                'jumlah' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_user' => 4,
                'id_produk' => 1,
                'jumlah' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_user' => 4,
                'id_produk' => 7,
                'jumlah' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
