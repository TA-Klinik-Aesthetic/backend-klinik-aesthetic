<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class FeedbackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_feedback')->insert([
            [
                'id_user' => 1, // Pastikan id_user ini sudah ada di tabel tb_user
                'rating' => 4,
                'teks_feedback' => 'Pelayanan dokter sangat baik dan ramah.',
                'balasan_feedback' => 'Terima kasih atas feedback Anda!',
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Tambahkan data dummy lainnya jika diperlukan
        ]);
    }
}
