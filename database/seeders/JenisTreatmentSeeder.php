<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisTreatmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed untuk tabel tb_jenis_treatment
        DB::table('tb_jenis_treatment')->insert([
            ['nama_jenis_treatment' => 'Face Treatment', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jenis_treatment' => 'Body Treatment', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jenis_treatment' => 'Eye Treatment', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jenis_treatment' => 'Lip Treatment', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jenis_treatment' => 'Injection', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
