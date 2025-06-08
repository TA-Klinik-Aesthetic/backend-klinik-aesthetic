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
                'foto_dokter' => 'https://images.unsplash.com/photo-1593636564519-5beeb8d3248f?q=80&w=1974&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'no_telp' => '081234567890',
                'email_dokter' => 'drranirahmawati@example.com',
                'NIP' => '1234567890',
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'nama_dokter' => 'Dr. Usman Yusuf',
                'foto_dokter' => 'https://plus.unsplash.com/premium_photo-1661764878654-3d0fc2eefcca?q=80&w=1974&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'no_telp' => '081234567450',
                'email_dokter' => 'usmanyusuf@example.com',
                'NIP' => '1234567890',
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'nama_dokter' => 'Dr. Alexis Kusuma',
                'foto_dokter' => 'https://images.unsplash.com/photo-1651008376811-b90baee60c1f?q=80&w=1974&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'no_telp' => '081234567374',
                'email_dokter' => 'alexiskusumadr@example.com',
                'NIP' => '1234567890',
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Tambahkan data dummy lainnya jika diperlukan
        ]);
    }
}
