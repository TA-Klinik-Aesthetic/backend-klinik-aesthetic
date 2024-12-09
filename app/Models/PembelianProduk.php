<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianProduk extends Model
{
    use HasFactory;

    protected $table = 'tb_pembelian_produk';

    protected $fillable = [
        'id_user',
        'tanggal_pembelian',
        'harga_total',
        'potongan_harga',
        'harga_akhir',
    ];
}
