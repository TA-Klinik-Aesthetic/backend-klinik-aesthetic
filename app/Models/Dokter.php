<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    use HasFactory;

    protected $table = 'tb_dokter'; // Nama tabel di database
    protected $primaryKey = 'id_dokter'; // Nama tabel di database

    protected $fillable = [
        'nama_dokter',
        'no_telp',
        'email_dokter',
        'NIP',
        'foto_dokter'
    ];

    // Relasi ke model Konsultasi
    public function konsultasi()
    {
        return $this->hasMany(Konsultasi::class, 'id_dokter');
    }

    // Relasi ke model detail booking treatment
    public function booking_treatment()
    {
        return $this->hasMany(BookingTreatment::class, 'id_dokter');
    }

    // Relasi ke model jadwal praktik dokter
    public function jadwal_praktik_dokter()
    {
        return $this->hasMany(JadwalPraktikDokter::class, 'id_dokter');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'id_dokter', 'id_dokter');
    }

    /**
     * Cek apakah dokter difavoritkan oleh user tertentu
     */
    public function isFavoritedBy($userId)
    {
        return $this->favorites()->where('id_user', $userId)->exists();
    }

    /**
     * Hitung jumlah favorit untuk dokter ini
     */
    public function getFavoritesCountAttribute()
    {
        return $this->favorites()->count();
    }
}
