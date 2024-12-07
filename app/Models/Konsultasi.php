<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Konsultasi extends Model
{
    use HasFactory;

    protected $table = 'tb_konsultasi'; // Nama tabel di database
    protected $fillable = [
        'id_user',
        'nama_pelanggan',
        'id_dokter',
        'waktu_konsultasi'
    ];

    // Relasi ke model User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // Relasi ke model Dokter
    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'id_dokter');
    }
}
