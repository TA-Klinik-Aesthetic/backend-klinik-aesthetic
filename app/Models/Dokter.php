<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    use HasFactory;

    use HasFactory;

    protected $table = 'tb_dokter'; // Nama tabel di database
    protected $fillable = [
        'nama_dokter',
        'no_telp',
        'email_dokter',
        'password',
        'nomor_izin_praktik'
    ];

    // Relasi ke model Konsultasi
    public function konsultasi()
    {
        return $this->hasMany(Konsultasi::class, 'id_dokter');
    }
}
