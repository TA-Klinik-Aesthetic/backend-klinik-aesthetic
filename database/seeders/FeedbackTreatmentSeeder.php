<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeedbackTreatmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_feedback_treatment')->insert([
            [
                'id_booking_treatment' => 1,
                'rating' => 5,
                'teks_feedback' => 'Pelayanan treatment sangat baik dan ramah.',
                'balasan_feedback' => 'Terima kasih atas feedback Anda!',
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Tambahkan data dummy lainnya jika diperlukan
        ]);
    }
}
