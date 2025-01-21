<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $table = 'tb_promo';

    protected $primaryKey = 'id_promo'; // Primary key tabel

    public $incrementing = true; // Pastikan primary key auto-increment
    protected $keyType = 'int'; // Tipe data primary key

    protected $fillable = [
        'nama_promo',
        'deskripsi_promo',
        'potongan_harga',
        'tanggal_mulai',
        'tanggal_berakhir',
        'gambar_promo',
        'status_promo',
    ];

    // Relasi ke Penjualan Produk
    public function penjualanProduk()
    {
        return $this->hasMany(PembelianProduk::class, 'id_promo');
    }
}
