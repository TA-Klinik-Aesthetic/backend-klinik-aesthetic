<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    use HasFactory;

    use HasFactory;

    protected $table = 'tb_dokter'; // Nama tabel di database
    protected $primaryKey = 'id_dokter'; // Nama tabel di database
    
    protected $fillable = [
        'nama_dokter',
        'no_telp',
        'email_dokter',
        'NIP'
    ];

    // Relasi ke model Konsultasi
    public function konsultasi()
    {
        return $this->hasMany(Konsultasi::class, 'id_dokter');
    }

    // Relasi ke model detail booking treatment
    public function detail_booking_treatment()
    {
        return $this->hasMany(DetailBookingTreatment::class, 'id_dokter');
    }

    // Relasi ke model jadwal praktik dokter
    public function jadwal_praktik_dokter()
    {
        return $this->hasMany(JadwalPraktikDokter::class, 'id_dokter');
    }
}
