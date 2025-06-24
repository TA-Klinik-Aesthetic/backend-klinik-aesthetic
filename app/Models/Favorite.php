<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $table = 'tb_favorite';
    protected $primaryKey = 'id_favorite';

    protected $fillable = [
        'id_user',
        'id_dokter',
        'id_produk',
        'id_treatment',
    ];

    // Relasi dengan User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Relasi dengan Dokter
    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'id_dokter', 'id_dokter');
    }

    // Relasi dengan Produk
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    // Relasi dengan Treatment
    public function treatment()
    {
        return $this->belongsTo(Treatment::class, 'id_treatment', 'id_treatment');
    }
}
