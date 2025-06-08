<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranProduk extends Model
{
    use HasFactory;

    protected $table = 'tb_pembayaran_produk';

    protected $primaryKey = 'id_pembayaran_produk';

    protected $fillable = [
        'id_penjualan_produk',
        'metode_pembayaran',
        'harga_akhir',
        'uang',
        'kembalian',
    ];

    protected $casts = [
        'harga_akhir' => 'decimal:2',
        'uang' => 'decimal:2',
        'kembalian' => 'decimal:2',
    ];

    // Relasi dengan PenjualanProduk
    public function penjualanProduk()
    {
        return $this->belongsTo(PembelianProduk::class, 'id_penjualan_produk');
    }
}
