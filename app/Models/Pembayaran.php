<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'tb_pembayaran';

    protected $primaryKey = 'id_pembayaran';

    protected $fillable = [
        'id_booking_treatment',
        'id_penjualan_produk',
        'uang',
        'kembalian',
        'metode_pembayaran',
        'status_pembayaran',
        'waktu_pembayaran',
        'payment_type',
        'transaction_id',
        'order_id',
        'snap_token',
        'snap_url',
        'payment_details',
    ];

    protected $casts = [
        'uang' => 'decimal:2',
        'kembalian' => 'decimal:2',
        'payment_details' => 'json',
    ];

    // Relasi dengan PenjualanProduk
    public function penjualanProduk()
    {
        return $this->belongsTo(PembelianProduk::class, 'id_penjualan_produk');
    }

    public function bookingTreatment()
    {
        return $this->belongsTo(BookingTreatment::class, 'id_booking_treatment');
    }
}
