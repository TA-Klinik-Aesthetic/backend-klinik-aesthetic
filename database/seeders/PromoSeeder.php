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
                'nama_promo'        => 'Lebaran Ceria',
                'jenis_promo'       => 'Produk',         // diubah menjadi Produk
                'deskripsi_promo'   => 'Nikmati potongan Rp50.000 selama Ramadan untuk setiap pembelian produk!',
                'tipe_potongan'     => 'Rupiah',
                'potongan_harga'    => 50000,
                'minimal_belanja'   => 0,
                'tanggal_mulai'     => '2025-04-10',
                'tanggal_berakhir'  => '2025-04-17',
                'gambar_promo'      => '',
                'status_promo'      => 'Aktif',
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'nama_promo'        => 'Summer Glow 2025',
                'jenis_promo'       => 'Produk',
                'deskripsi_promo'   => 'Diskon 25% untuk setiap pembelian produk saat musim panas!',
                'tipe_potongan'     => 'Diskon',
                'potongan_harga'    => 25,
                'minimal_belanja'   => 150000,
                'tanggal_mulai'     => '2025-07-01',
                'tanggal_berakhir'  => '2025-07-31',
                'gambar_promo'      => '',
                'status_promo'      => 'Aktif',
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'nama_promo'        => 'Ulang Tahun Klinik Ke-1',
                'jenis_promo'       => 'Treatment',      // diubah menjadi Treatment
                'deskripsi_promo'   => 'Rayakan ulang tahun kami dengan potongan Rp100.000 untuk setiap pembelian treatment',
                'tipe_potongan'     => 'Rupiah',
                'potongan_harga'    => 100000,
                'minimal_belanja'   => 200000,
                'tanggal_mulai'     => '2025-07-17',
                'tanggal_berakhir'  => '2025-08-31',
                'gambar_promo'      => '',
                'status_promo'      => 'Aktif',
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'nama_promo'        => 'Promo Natal & Tahun Baru',
                'jenis_promo'       => 'Treatment',
                'deskripsi_promo'   => 'Diskon 50% untuk setiap pembelian treatment dalam menyambut Natal & Tahun Baru!',
                'tipe_potongan'     => 'Diskon',
                'potongan_harga'    => 50,
                'minimal_belanja'   => 100000,
                'tanggal_mulai'     => '2025-12-20',
                'tanggal_berakhir'  => '2026-01-05',
                'gambar_promo'      => '',
                'status_promo'      => 'Aktif',
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ]);
    }
}
