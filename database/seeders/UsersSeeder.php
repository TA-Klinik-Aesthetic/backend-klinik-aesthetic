<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Import Carbon untuk timestamp

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
                'tanggal_lahir' => '1990-05-15',
                'jenis_kelamin' => 'Laki-laki',
                'role' => 'pelanggan',
                'email_verified_at' => Carbon::now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_user' => 'Ezra',
                'no_telp' => '089765452765',
                'email' => 'ezra@gmail.com',
                'password' => Hash::make('password123'),
                'tanggal_lahir' => '1995-08-22',
                'jenis_kelamin' => 'Laki-laki',
                'role' => 'pelanggan',
                'email_verified_at' => Carbon::now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_user' => 'Nata Nara',
                'no_telp' => '082345678901',
                'email' => 'natanara@gmail.com',
                'password' => Hash::make('password456'),
                'tanggal_lahir' => '1988-12-03',
                'jenis_kelamin' => 'Laki-laki',
                'role' => 'pelanggan',
                'email_verified_at' => Carbon::now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_user' => 'Alice Johnson',
                'no_telp' => '083456789012',
                'email' => 'alicejohnson@example.com',
                'password' => Hash::make('password789'),
                'tanggal_lahir' => '1992-03-30',
                'jenis_kelamin' => 'Perempuan',
                'role' => 'front office',
                'email_verified_at' => Carbon::now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_user' => 'Alice',
                'no_telp' => '083456789013',
                'email' => 'alicejohn@example.com',
                'password' => Hash::make('alice123'),
                'tanggal_lahir' => '1992-03-30',
                'jenis_kelamin' => 'Perempuan',
                'role' => 'kasir',
                'email_verified_at' => Carbon::now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
