<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DokterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_dokter')->insert([
            [
                'nama_dokter' => 'Dr. Rani Rahmawati',
                'no_telp' => '081234567890',
                'email_dokter' => 'drraniu@example.com',
                'NIP' => '1234567890',
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'nama_dokter' => 'Dr. Rahmat suprianto',
                'no_telp' => '081234567450',
                'email_dokter' => 'drranie@example.com',
                'NIP' => '1234567890',
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'nama_dokter' => 'Dr. Rudja',
                'no_telp' => '081234567374',
                'email_dokter' => 'drrania@example.com',
                'NIP' => '1234567890',
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Tambahkan data dummy lainnya jika diperlukan
        ]);
    }
}
