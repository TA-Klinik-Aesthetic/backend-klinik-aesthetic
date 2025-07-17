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
                'nama_beautician' => 'Dr. Carmelia Putri',
                'no_telp' => '081234567789',
                'email_beautician' => 'carmelia@gmail.com',
                'NIP' => '1234567891',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama_beautician' => 'Dr. Sinta Hadiguna',
                'no_telp' => '089765463546',
                'email_beautician' => 'sinta@gmail.com',
                'NIP' => '12309878987',
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Tambahkan data dummy lainnya jika diperlukan
        ]);
    }
}
