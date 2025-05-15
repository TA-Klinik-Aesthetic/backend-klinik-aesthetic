<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kompensasi extends Model
{
    use HasFactory;

    protected $table = 'tb_kompensasi';

    protected $primaryKey = 'id_kompensasi';

    public $timestamps = true;

    protected $fillable = [
        'nama_kompensasi',
        'deskripsi_kompensasi',
        'id_treatment'
    ];

    // Relasi dengan model Komplain
    public function treatment()
    {
        return $this->belongsTo(Treatment::class, 'id_treatment');
    }

    public function kompensasiDiberikan()
    {
        return $this->hasMany(KompensasiDiberikan::class, 'id_kompensasi');
    }
}
