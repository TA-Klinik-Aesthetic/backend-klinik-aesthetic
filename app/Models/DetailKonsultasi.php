<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailKonsultasi extends Model
{
    use HasFactory;

    protected $table = 'tb_detail_konsultasi'; // Nama tabel di database
    protected $fillable = [
        'keluhan_pelanggan',
        'diagnosa_dokter',
        'saran_tindakan',
        'resep_obat',
        'catatan_tambahan'
    ];

    // Relasi ke model Konsultasi
    public function konsultasi()
    {
        return $this->hasMany(Konsultasi::class, 'id_detail_konsultasi');
    }
}
