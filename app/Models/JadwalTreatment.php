<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalTreatment extends Model
{
    use HasFactory;

    protected $table = 'tb_jadwal_treatment';
    protected $primaryKey = 'id_jadwal_treatment';
    public $timestamps = false; // sesuaikan kalau ada created_at/updated_at

    protected $fillable = [
        'tanggal_treatment',
    ];

    /**
     * Detail jadwal (waktu tersedia)
     */
    public function details()
    {
        return $this->hasMany(DetailJadwalTreatment::class, 'id_jadwal_treatment', 'id_jadwal_treatment');
    }
}
