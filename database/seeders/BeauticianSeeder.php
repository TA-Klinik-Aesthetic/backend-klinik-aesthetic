<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class BeauticianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_beautician')->insert([
            [
                'nama_beautician' => 'Dr. Rian Rahmat',
                'no_telp' => '081234567789',
                'email_beautician' => 'drrian@example.com',
                'NIP' => '1234567891',
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Tambahkan data dummy lainnya jika diperlukan
        ]);
    }
}
