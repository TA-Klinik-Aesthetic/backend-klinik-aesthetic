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
        'deskripsi_kompensasi'
    ];

    // Relasi dengan model Komplain
    public function komplain()
    {
        return $this->hasMany(Komplain::class, 'id_kompensasi', 'id_kompensasi');
    }
}
