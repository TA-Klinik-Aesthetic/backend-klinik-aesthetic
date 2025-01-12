<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $table = 'tb_promo'; // Menyebutkan nama tabel jika berbeda

    protected $primaryKey = 'id_promo'; // Tentukan primary key (jika tidak default id)

    protected $fillable = [
        'judul_promo',
        'deskripsi_promo',
        'keterangan_promo',
        'tenggat_waktu_promosi',
    ];
}
