<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class DetailKonsultasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_detail_konsultasi')->insert([
            [
                'id_konsultasi' => 1,
                // 'keluhan_pelanggan' => 'Sakit kepala dan demam',
                'diagnosis' => 'Tekstur kulit kasar',
                'saran_tindakan' => 'Lakukan treatment eksfoliasi kimia ringan (AHA 10%) sekali dan gunakan pelembap berbahan hyaluronic acid dua kali sehari',
                'id_treatment' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id_konsultasi' => 1,
                // 'keluhan_pelanggan' => 'Sakit kepala dan demam',
                'diagnosis' => 'Komedo tertutup di hidung',
                'saran_tindakan' => 'Lakukan treatment ekstraksi komedo profesional sekali seminggu; gunakan serum niacinamide 5% setiap malam',
                'id_treatment' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Tambahkan data dummy lainnya jika diperlukan
        ]);
    }
}
