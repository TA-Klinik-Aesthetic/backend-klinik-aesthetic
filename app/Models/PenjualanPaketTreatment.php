<?php
// app/Models/PenjualanPaketTreatment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PenjualanPaketTreatment extends Model
{
    use HasFactory;

    protected $table      = 'tb_penjualan_paket_treatment';
    protected $primaryKey = 'id_penjualan_paket_treatment';
    public    $timestamps = true;

    protected $fillable = [
        'id_user',
        'tanggal_pembelian',
        'harga_total',
        'id_promo',
        'potongan_harga',
        'besaran_pajak',
        'harga_akhir',
    ];

    protected $casts = [
        'tanggal_pembelian' => 'datetime',
        'harga_total'       => 'decimal:2',
        'potongan_harga'    => 'decimal:2',
        'besaran_pajak'     => 'decimal:2',
        'harga_akhir'       => 'decimal:2',
    ];

    // --- Relasi ---

    // Penjualan milik seorang user (tb_user)
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Opsional: promo (tb_promo)
    public function promo()
    {
        return $this->belongsTo(Promo::class, 'id_promo', 'id_promo');
    }

    // Detail penjualan (tb_detail_penjualan_paket_treatment)
    public function details()
    {
        return $this->hasMany(DetailPenjualanPaketTreatment::class, 'id_penjualan_paket_treatment', 'id_penjualan_paket_treatment');
    }

    // Pembayaran (tb_pembayaran) – asumsi 1 penjualan 1 pembayaran
    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'id_penjualan_paket_treatment', 'id_penjualan_paket_treatment');
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
