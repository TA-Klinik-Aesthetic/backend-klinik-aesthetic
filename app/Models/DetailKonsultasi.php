<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailKonsultasi extends Model
{
    use HasFactory;

    protected $table = 'tb_detail_konsultasi'; // Nama tabel yang sesuai

    // Tentukan kolom yang bisa diisi secara massal
    protected $fillable = [
        'id_konsultasi',
        'keluhan_pelanggan',
        'saran_tindakan',
    ];

    public function konsultasi()
    {
        return $this->belongsTo(Konsultasi::class, 'id_konsultasi', 'id');
    }
}
