<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisTreatment extends Model
{
    use HasFactory;

    protected $table = 'tb_jenis_treatment';

    protected $primaryKey = 'id_jenis_treatment';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'nama_jenis_treatment',
    ];

    // Relasi ke model Treatment
    public function treatment()
    {
        return $this->hasMany(Treatment::class, 'id_jenis_treatment', 'id_jenis_treatment');
    }
}