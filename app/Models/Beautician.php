<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beautician extends Model
{
    use HasFactory;

    protected $table = 'tb_beautician'; // Nama tabel di database
    protected $primaryKey = 'id_beautician'; // Nama tabel di database

    protected $fillable = [
        'nama_beautician',
        'no_telp',
        'email_beautician',
        'NIP'
    ];

    // Relasi ke model detail booking treatment
    public function detail_booking_treatment()
    {
        return $this->hasMany(DetailBookingTreatment::class, 'id_beautician', 'id_beautician');
    }

    // Relasi ke model jadwal praktik dokter
    public function jadwal_praktik_beautician()
    {
        return $this->hasMany(JadwalPraktikBeautician::class, 'id_beautician');
    }
}
