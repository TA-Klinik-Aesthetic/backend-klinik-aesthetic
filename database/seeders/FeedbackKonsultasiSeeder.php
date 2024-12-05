<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class FeedbackKonsultasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_feedback_konsultasi')->insert([
            [
                'id_konsultasi' => 1,
                'rating' => 5,
                'teks_feedback' => 'Pelayanan dokter sangat baik dan ramah.',
                'balasan_feedback' => 'Terima kasih atas feedback Anda!',
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Tambahkan data dummy lainnya jika diperlukan
        ]);
    }
}
