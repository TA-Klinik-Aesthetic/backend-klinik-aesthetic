<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisTreatment extends Model
{
    use HasFactory;

    protected $table = 'tb_jenis_treatment'; // Nama tabel di database
    protected $fillable = [
        'nama_jenis_treatment',
    ];

    // Relasi ke model Treatment
    public function treatment()
    {
        return $this->hasMany(Treatment::class, 'id_jenis_treatment');
    }
}
