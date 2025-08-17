<?php
// app/Models/DetailPenjualanPaketTreatment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPenjualanPaketTreatment extends Model
{
    protected $table      = 'tb_detail_penjualan_paket_treatment';
    protected $primaryKey = 'id_detail_penjualan_paket_treatment';
    public    $timestamps = true;

    protected $fillable = [
        'id_penjualan_paket_treatment',
        'id_paket_treatment',
        'harga_paket_treatment',
    ];

    protected $casts = [
        'harga_paket_treatment' => 'decimal:2',
    ];

    // --- Relasi ---

    // Detail milik header penjualan
    public function penjualan()
    {
        return $this->belongsTo(PenjualanPaketTreatment::class, 'id_penjualan_paket_treatment', 'id_penjualan_paket_treatment');
    }

    // Detail mengacu ke master paket
    public function paket()
    {
        return $this->belongsTo(PaketTreatment::class, 'id_paket_treatment', 'id_paket_treatment');
    }
}
