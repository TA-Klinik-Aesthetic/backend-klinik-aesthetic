<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPraktikBeautician extends Model
{
    use HasFactory;

    protected $table = 'tb_jadwal_praktik_beautician'; // Nama tabel di database
    protected $primaryKey = 'id_jadwal_praktik_beautician'; // Nama kolom primary key

    protected $fillable = [
        'id_beautician',
        'hari',
        'tgl_kerja',
        'jam_mulai',
        'jam_selesai'
    ];

    // Relasi ke model Beautician
    public function beautician()
    {
        return $this->belongsTo(Beautician::class, 'id_beautician', 'id_beautician');
    }
}
