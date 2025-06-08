<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPembelianProduk extends Model
{
    use HasFactory;

    protected $table = 'tb_detail_penjualan_produk';

    protected $primaryKey = 'id_detail_penjualan_produk'; // Primary key tabel

    public $incrementing = true; // Pastikan primary key auto-increment
    protected $keyType = 'int'; // Tipe data primary key

    protected $fillable = [
        'id_penjualan_produk',
        'id_produk',
        'jumlah_produk',
        'harga_penjualan_produk',
    ];

        // Relasi ke Pembelian
        public function pembelian()
        {
            return $this->belongsTo(PembelianProduk::class, 'id_penjualan_produk');
        }

        // Relasi ke Produk
        public function produk()
        {
            return $this->belongsTo(Produk::class, 'id_produk');
        }
}
