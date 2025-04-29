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
                'id_kategori' => 1,
                'nama_produk' => 'Erha Truwhite Algae Complex & Cucumber Extract Brightening Eye Serum 15G',
                'deskripsi_produk' => 'Serum mata untuk melembapkan, menyamarkan kantung mata, dan mencerahkan lingkaran hitam.',
                'harga_produk' => 25000,
                'stok_produk' => 50,
                'status_produk' => 'Tersedia',
                'gambar_produk' => 'https://www.erhastore.co.id/media/catalog/product/cache/d7666cf6b09b1c021bd15e04ee5b4d8f/t/r/truwhite_eye_serum.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_kategori' => 2,
                'nama_produk' => 'His Erha Booster Facial Wash 100gr - Sabun Pencerah Wajah Pria',
                'deskripsi_produk' => 'Pembersih wajah yang diformulasikan khusus untuk semua jenis kulit Pria yang befungsi untuk membantu mencerahkan dan menjaga kelembapan kulit.',
                'harga_produk' => 50000,
                'stok_produk' => 80,
                'status_produk' => 'Tersedia',
                'gambar_produk' => 'https://www.erhastore.co.id/media/catalog/product/cache/ff48c211cc0443c9467d58e2065beae1/h/i/hiserha_booster_fw.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_kategori' => 3,
                'nama_produk' => 'ERHAIR HairGrow Shampoo 100ml',
                'deskripsi_produk' => 'Berfungsi untuk mengatasi rambut rontok, mengurangi kerontokan dan menumbuhkan rambut baru pada Pria dan Wanita.',
                'harga_produk' => 75000.00,
                'stok_produk' => 150,
                'status_produk' => 'Tersedia',
                'gambar_produk' => 'https://www.erhastore.co.id/media/catalog/product/cache/d7666cf6b09b1c021bd15e04ee5b4d8f/e/r/erhair_shampoo_100_ml.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_kategori' => 4,
                'nama_produk' => 'Erha Perfect Shield Sunscreen Spray 90 Ml',
                'deskripsi_produk' => 'Tabir surya perlindungan ganda terhadap sinar UVA dan UVB dengan SPF 50/ PA++++ untuk melindungi kulit dari sinar matahari.',
                'harga_produk' => 13000.00,
                'stok_produk' => 200,
                'status_produk' => 'Tersedia',
                'gambar_produk' => 'https://www.erhastore.co.id/media/catalog/product/cache/ff48c211cc0443c9467d58e2065beae1/e/r/erha_perfect_shield_suncreen_spray_90_ml.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_kategori' => 5,
                'nama_produk' => 'ERHA Acneact Acne Spot Gel 10gr',
                'deskripsi_produk' => 'Melawan bakteri penyebab jerawat, mempercepat pengeringan jerawat, dan membantu eksfoliasi sel kulit mati, serta menyamarkan noda hitam bekas jerawat',
                'harga_produk' => 85000.00,
                'stok_produk' => 90,
                'status_produk' => 'Tersedia',
                'gambar_produk' => 'lipstick_matte.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_kategori' => 1,
                'nama_produk' => 'Erha 3 Balancing Toner 60Ml',
                'deskripsi_produk' => 'Berfungsi untuk mengangkat sel kulit mati, menyeimbangkan pH kulit dan mempersiapkan kulit untuk penyerapan bahan aktif perawatan wajah selanjutnya.',
                'harga_produk' => 70000.00,
                'stok_produk' => 150,
                'status_produk' => 'Tersedia',
                'gambar_produk' => 'https://www.erhastore.co.id/media/catalog/product/cache/ff48c211cc0443c9467d58e2065beae1/e/r/erha21_balancing_toner_1_.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_kategori' => 1,
                // 'id_jenis_treatment' => 1,
                'nama_produk' => 'ERHA21 Acne Spot Treatment Calamine & Sulfur 5%',
                'deskripsi_produk' => 'Krim perawatan untuk mengatasi masalah jerawat ringan hingga sedang, ampuh melawan bakteri penyebab jerawat serta membantu menenangkan iritasi ringan dan kemerahan akibat jerawat.',
                'harga_produk' => 150000.00,
                'stok_produk' => 100,
                'status_produk' => 'Tersedia',
                'gambar_produk' => 'moihttps://www.erhastore.co.id/media/catalog/product/cache/d7666cf6b09b1c021bd15e04ee5b4d8f/e/r/erha21_acne_spot_treatment.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
