<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailJadwalTreatment extends Model
{
    protected $table = 'tb_detail_jadwal_treatment';
    protected $primaryKey = 'id_detail_jadwal_treatment';
    public $timestamps = false;

    protected $fillable = [
        'id_jadwal_treatment',
        'waktu_tersedia',
        'maks_booking',
        'status_jadwal',
    ];

    /**
     * Kembali ke master jadwal
     */
    public function jadwal()
    {
        return $this->belongsTo(JadwalTreatment::class, 'id_jadwal_treatment', 'id_jadwal_treatment');
    }
}
