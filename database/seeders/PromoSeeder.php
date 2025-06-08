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
                'jenis_promo' => 'Produk',
                'deskripsi_promo' => 'Diskon 50 ribu untuk semua produk!',
                'tipe_potongan' => 'Rupiah',
                'potongan_harga' => 50000,
                'minimal_belanja' => 30000,
                'tanggal_mulai' => '2025-01-01',
                'tanggal_berakhir' => '2025-01-31',
                'gambar_promo' => 'https://images.unsplash.com/photo-1562809683-67b524a5ce11?q=80&w=2683&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'status_promo' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_promo' => 'Promo Spesial Bulan Ini',
                'jenis_promo' => 'Treatment',
                'deskripsi_promo' => 'Dapatkan potongan harga Rp25.000 untuk pembelian minimal Rp100.000.',
                'tipe_potongan' => 'Rupiah',
                'potongan_harga' => 25000,
                'minimal_belanja' => 10000,
                'tanggal_mulai' => '2025-02-01',
                'tanggal_berakhir' => '2025-02-28',
                'gambar_promo' => 'https://images.unsplash.com/photo-1562809683-67b524a5ce11?q=80&w=2683&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'status_promo' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_promo' => 'Diskon Member Baru',
                'jenis_promo' => 'Produk',
                'deskripsi_promo' => 'Diskon 20% untuk semua produk!',
                'tipe_potongan' => 'Diskon',
                'potongan_harga' => 20,
                'minimal_belanja' => 50000,
                'tanggal_mulai' => '2025-05-01',
                'tanggal_berakhir' => '2025-05-31',
                'gambar_promo' => 'https://images.unsplash.com/photo-1562809683-67b524a5ce11?q=80&w=2683&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'status_promo' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_promo' => 'Diskon Percantik Wajahmu',
                'jenis_promo' => 'Treatment',
                'deskripsi_promo' => 'Diskon 15% untuk semua produk!',
                'tipe_potongan' => 'Diskon',
                'potongan_harga' => 15,
                'minimal_belanja' => 50000,
                'tanggal_mulai' => '2025-05-01',
                'tanggal_berakhir' => '2025-05-31',
                'gambar_promo' => 'https://images.unsplash.com/photo-1562809683-67b524a5ce11?q=80&w=2683&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'status_promo' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
