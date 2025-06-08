<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarisStok extends Model
{
    use HasFactory;

    // protected $table = 'tb_inventaris_stok';
    // protected $primaryKey = 'id_inventaris_stok';

    // protected $fillable = [
    //     'id_produk',
    //     'status_perubahan',
    //     'jumlah_perubahan',
    //     'waktu_perubahan',
    // ];

    // public $timestamps = true;

    // // Relasi ke produk
    // public function produk()
    // {
    //     return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    // }
}
