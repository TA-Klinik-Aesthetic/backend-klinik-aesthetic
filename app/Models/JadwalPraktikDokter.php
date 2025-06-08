<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPraktikDokter extends Model
{
    use HasFactory;

    protected $table = 'tb_jadwal_praktik_dokter'; // Nama tabel di database
    protected $primaryKey = 'id_jadwal_praktik_dokter'; // Nama kolom primary key

    protected $fillable = [
        'id_dokter',
        'tgl_kerja',
        'jam_mulai',
        'jam_selesai'
    ];

    // Relasi ke model Dokter
    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'id_dokter', 'id_dokter');
    }
}
