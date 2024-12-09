<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_produk')->insert([
            [
                'id_kategori' => 1, // Skincare
                'nama_produk' => 'Moisturizer',
                'deskripsi_produk' => 'Krim pelembap untuk kulit kering.',
                'harga_produk' => 150000.00,
                'stok_produk' => 100,
                'status_produk' => 'Tersedia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_kategori' => 1, // Skincare
                'nama_produk' => 'Sunscreen SPF 50',
                'deskripsi_produk' => 'Tabir surya dengan perlindungan maksimal.',
                'harga_produk' => 120000.00,
                'stok_produk' => 80,
                'status_produk' => 'Tersedia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_kategori' => 2, // Makeup
                'nama_produk' => 'Lipstick Matte',
                'deskripsi_produk' => 'Lipstik matte tahan lama dengan warna natural.',
                'harga_produk' => 75000.00,
                'stok_produk' => 150,
                'status_produk' => 'Tersedia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_kategori' => 3, // Haircare
                'nama_produk' => 'Shampoo Anti Ketombe',
                'deskripsi_produk' => 'Shampoo yang membantu menghilangkan ketombe.',
                'harga_produk' => 45000.00,
                'stok_produk' => 200,
                'status_produk' => 'Tersedia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_kategori' => 4, // Bodycare
                'nama_produk' => 'Body Lotion',
                'deskripsi_produk' => 'Lotion pelembap kulit dengan aroma bunga.',
                'harga_produk' => 85000.00,
                'stok_produk' => 90,
                'status_produk' => 'Tersedia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_kategori' => 5, // Fragrance
                'nama_produk' => 'Parfum Floral',
                'deskripsi_produk' => 'Parfum dengan aroma bunga yang menyegarkan.',
                'harga_produk' => 250000.00,
                'stok_produk' => 50,
                'status_produk' => 'Tersedia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
