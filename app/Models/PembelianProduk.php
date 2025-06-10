<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianProduk extends Model
{
    use HasFactory;

    protected $table = 'tb_penjualan_produk';

    protected $primaryKey = 'id_penjualan_produk'; // Primary key tabel

    public $incrementing = true; // Pastikan primary key auto-increment
    protected $keyType = 'int'; // Tipe data primary key

    protected $fillable = [
        'id_user',
        'tanggal_pembelian',
        'harga_total',
        'id_promo',
        'potongan_harga',
        'besaran_pajak',
        'harga_akhir',
    ];

    // Relasi ke Detail Pembelian
    public function detailPembelian()
    {
        return $this->hasMany(DetailPembelianProduk::class, 'id_penjualan_produk');
    }

    // Relasi ke model User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Relasi ke model Promo
    public function promo()
    {
        return $this->belongsTo(Promo::class, 'id_promo', 'id_promo');
    }

    public function pembayaranProduk()
    {
        return $this->hasOne(Pembayaran::class, 'id_penjualan_produk', 'id_penjualan_produk');
    }
}
