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
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_promo' => 'Promo Tahun Baru',
                'deskripsi_promo' => 'Diskon spesial 30% untuk menyambut tahun baru.',
                'diskon_promo' => 30.00,
                'tanggal_mulai' => '2025-01-01',
                'tanggal_berakhir' => '2025-01-10',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_promo' => 'Promo Hari Kartini',
                'deskripsi_promo' => 'Diskon 20% untuk produk kecantikan khusus wanita.',
                'diskon_promo' => 20.00,
                'tanggal_mulai' => '2025-04-21',
                'tanggal_berakhir' => '2025-04-30',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_promo' => 'Diskon Ramadhan',
                'deskripsi_promo' => 'Potongan harga hingga 25% untuk produk pilihan selama Ramadhan.',
                'diskon_promo' => 25.00,
                'tanggal_mulai' => '2025-03-10',
                'tanggal_berakhir' => '2025-04-10',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_promo' => 'Promo Hari Kemerdekaan',
                'deskripsi_promo' => 'Diskon 17% untuk semua produk dalam rangka Hari Kemerdekaan.',
                'diskon_promo' => 17.00,
                'tanggal_mulai' => '2025-08-17',
                'tanggal_berakhir' => '2025-08-31',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_promo' => 'Promo Flash Sale',
                'deskripsi_promo' => 'Diskon hingga 70% hanya selama 24 jam!',
                'diskon_promo' => 70.00,
                'tanggal_mulai' => '2025-05-01',
                'tanggal_berakhir' => '2025-05-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_promo' => 'Diskon Lebaran',
                'deskripsi_promo' => 'Nikmati potongan harga hingga 40% untuk merayakan Lebaran.',
                'diskon_promo' => 40.00,
                'tanggal_mulai' => '2025-04-10',
                'tanggal_berakhir' => '2025-04-25',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($promos as $promo) {
            Promo::create($promo);
        }
    }
}
