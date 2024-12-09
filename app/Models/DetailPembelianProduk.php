<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TbDetailPembelianProduk extends Model
{
    use HasFactory;

    protected $table = 'tb_detail_pembelian_produk';
    protected $fillable = [
        'id_pembelian_produk',
        'id_produk',
        'jumlah_produk',
        'harga_pembelian_produk'
    ];

    public function pembelian()
    {
        return $this->belongsTo(PembelianProduk::class, 'id_pembelian_produk');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }
}
