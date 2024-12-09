<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens; // Tambahkan ini

class User extends Authenticatable
{
    use HasApiTokens, HasFactory; // Pastikan HasApiTokens ada di sini

    protected $table = 'tb_user'; // Nama tabel di database
    protected $primaryKey = 'id_user'; // Nama kolom primary key
    
    protected $fillable = [
        'nama_user',
        'no_telp',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}

