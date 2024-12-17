<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Promo;

class PromoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $promos = [
            [
                'nama_promo' => 'Diskon Akhir Tahun',
                'deskripsi_promo' => 'Nikmati diskon hingga 50% untuk semua produk selama akhir tahun!',
                'diskon_promo' => 50.00,
                'tanggal_mulai' => '2024-12-01',
                'tanggal_berakhir' => '2024-12-31',
                'gambar_promo' => 'https://img.freepik.com/free-vector/season-sale-special-offer-background_79603-1414.jpg?t=st=1734360871~exp=1734364471~hmac=f995b81c9a40c345fa5c947fdfac0dae4efdc2058b937df1657fa43644485b00&w=900',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_promo' => 'Promo Tahun Baru',
                'deskripsi_promo' => 'Diskon spesial 30% untuk menyambut tahun baru.',
                'diskon_promo' => 30.00,
                'tanggal_mulai' => '2025-01-01',
                'tanggal_berakhir' => '2025-01-10',
                'gambar_promo' => 'https://img.freepik.com/free-vector/promotion-fashion-tropical-banner_1188-157.jpg?t=st=1734360944~exp=1734364544~hmac=132f6bf86662fcbecb181503780bd734f5181eb8f766cddaff365b01ec19550c&w=996',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_promo' => 'Promo Hari Kartini',
                'deskripsi_promo' => 'Diskon 20% untuk produk kecantikan khusus wanita.',
                'diskon_promo' => 20.00,
                'tanggal_mulai' => '2025-04-21',
                'tanggal_berakhir' => '2025-04-30',
                'gambar_promo' => 'https://img.freepik.com/free-vector/promotion-fashion-tropical-banner_1188-157.jpg?t=st=1734360944~exp=1734364544~hmac=132f6bf86662fcbecb181503780bd734f5181eb8f766cddaff365b01ec19550c&w=996',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_promo' => 'Diskon Ramadhan',
                'deskripsi_promo' => 'Potongan harga hingga 25% untuk produk pilihan selama Ramadhan.',
                'diskon_promo' => 25.00,
                'tanggal_mulai' => '2025-03-10',
                'tanggal_berakhir' => '2025-04-10',
                'gambar_promo' => 'https://img.freepik.com/free-vector/season-sale-special-offer-background_79603-1414.jpg?t=st=1734360871~exp=1734364471~hmac=f995b81c9a40c345fa5c947fdfac0dae4efdc2058b937df1657fa43644485b00&w=900',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_promo' => 'Promo Hari Kemerdekaan',
                'deskripsi_promo' => 'Diskon 17% untuk semua produk dalam rangka Hari Kemerdekaan.',
                'diskon_promo' => 17.00,
                'tanggal_mulai' => '2025-08-17',
                'tanggal_berakhir' => '2025-08-31',
                'gambar_promo' => 'https://img.freepik.com/free-vector/promotion-fashion-tropical-banner_1188-157.jpg?t=st=1734360944~exp=1734364544~hmac=132f6bf86662fcbecb181503780bd734f5181eb8f766cddaff365b01ec19550c&w=996',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_promo' => 'Promo Flash Sale',
                'deskripsi_promo' => 'Diskon hingga 70% hanya selama 24 jam!',
                'diskon_promo' => 70.00,
                'tanggal_mulai' => '2025-05-01',
                'tanggal_berakhir' => '2025-05-01',
                'gambar_promo' => 'https://img.freepik.com/free-vector/season-sale-special-offer-background_79603-1414.jpg?t=st=1734360871~exp=1734364471~hmac=f995b81c9a40c345fa5c947fdfac0dae4efdc2058b937df1657fa43644485b00&w=900',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_promo' => 'Diskon Lebaran',
                'deskripsi_promo' => 'Nikmati potongan harga hingga 40% untuk merayakan Lebaran.',
                'diskon_promo' => 40.00,
                'tanggal_mulai' => '2025-04-10',
                'tanggal_berakhir' => '2025-04-25',
                'gambar_promo' => 'https://img.freepik.com/free-vector/season-sale-special-offer-background_79603-1414.jpg?t=st=1734360871~exp=1734364471~hmac=f995b81c9a40c345fa5c947fdfac0dae4efdc2058b937df1657fa43644485b00&w=900',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
    }
}
