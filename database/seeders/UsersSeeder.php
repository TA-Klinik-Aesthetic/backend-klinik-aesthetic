<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_user')->insert([
            [
                'nama_user' => 'John Doe',
                'no_telp' => '081234567890',
                'email' => 'johndoe@example.com',
                'password' => Hash::make('password123'),
                'role' => 'pelanggan',
            ],
            [
                'nama_user' => 'Jane Smith',
                'no_telp' => '082345678901',
                'email' => 'janesmith@example.com',
                'password' => Hash::make('password456'),
                'role' => 'pelanggan',
            ],
            [
                'nama_user' => 'Alice Johnson',
                'no_telp' => '083456789012',
                'email' => 'alicejohnson@example.com',
                'password' => Hash::make('password789'),
                'role' => 'front office',
            ],
        ]);
    }
}
