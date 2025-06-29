<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Konsultasi extends Model
{
    use HasFactory;

    protected $table = 'tb_konsultasi'; // Nama tabel di database
    protected $primaryKey = 'id_konsultasi'; // Nama tabel di database

    protected $fillable = [
        'id_user',
        'id_dokter',
        'waktu_konsultasi',
        'keluhan_pelanggan',
        'pemeriksaan_fisik',
        'status_booking_konsultasi'
    ];

    // Relasi ke model User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Relasi ke model Dokter
    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'id_dokter', 'id_dokter');
    }

    // Relasi dengan model DetailKonsultasi
    public function detail_konsultasi()
    {
        return $this->hasMany(DetailKonsultasi::class, 'id_konsultasi', 'id_konsultasi');
    }
}
