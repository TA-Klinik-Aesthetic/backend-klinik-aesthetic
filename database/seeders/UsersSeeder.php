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
                'foto_profil' => 'https://images.unsplash.com/photo-1639747280804-dd2d6b3d88ac?q=80&w=2574&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'tanggal_lahir' => '1990-05-15',
                'role' => 'pelanggan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_user' => 'Ezra',
                'no_telp' => '089765452765',
                'email' => 'ezra@gmail.com',
                'password' => Hash::make('password123'),
                'foto_profil' => 'https://plus.unsplash.com/premium_photo-1689977968861-9c91dbb16049?q=80&w=2670&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'tanggal_lahir' => '1995-08-22',
                'role' => 'pelanggan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_user' => 'Nata Nara',
                'no_telp' => '082345678901',
                'email' => 'natanara@gmail.com',
                'password' => Hash::make('password456'),
                'foto_profil' => 'https://images.unsplash.com/photo-1649057349440-38c14e985208?q=80&w=2660&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'tanggal_lahir' => '1988-12-03',
                'role' => 'pelanggan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_user' => 'Alice Johnson',
                'no_telp' => '083456789012',
                'email' => 'alicejohnson@example.com',
                'password' => Hash::make('password789'),
                'foto_profil' => 'https://images.unsplash.com/photo-1598550880863-4e8aa3d0edb4?q=80&w=2727&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'tanggal_lahir' => '1992-03-30',
                'role' => 'front office',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
