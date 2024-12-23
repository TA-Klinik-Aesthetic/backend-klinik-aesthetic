<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianProduk extends Model
{
    use HasFactory;

    protected $table = 'tb_pembelian_produk';

    protected $primaryKey = 'id_pembelian_produk'; // Primary key tabel

    public $incrementing = true; // Pastikan primary key auto-increment
    protected $keyType = 'int'; // Tipe data primary key

    protected $fillable = [
        'id_user',
        'tanggal_pembelian',
        'harga_total',
        'potongan_harga',
        'harga_akhir',
    ];

    // Relasi ke Detail Pembelian
    public function detailPembelian()
    {
        return $this->hasMany(DetailPembelianProduk::class, 'id_pembelian_produk');
    }
}
