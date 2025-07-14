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
        // Tambahan field Midtrans yang diperlukan
        'transaction_status',
        'va_number',
        'bank',
        'gross_amount',
        'midtrans_response',
    ];

    protected $casts = [
        'uang' => 'decimal:2',
        'kembalian' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'payment_details' => 'json',
        'midtrans_response' => 'json',
        'waktu_pembayaran' => 'datetime',
    ];

    // Relasi dengan PenjualanProduk - PERBAIKAN
    public function penjualanProduk()
    {
        return $this->belongsTo(PembelianProduk::class, 'id_penjualan_produk', 'id_penjualan_produk');
    }

    // Relasi dengan BookingTreatment - PERBAIKAN
    public function bookingTreatment()
    {
        return $this->belongsTo(BookingTreatment::class, 'id_booking_treatment', 'id_booking_treatment');
    }

    // Helper methods untuk status
    public function isPending()
    {
        return $this->status_pembayaran === 'Pending';
    }

    public function isSuccess()
    {
        return $this->status_pembayaran === 'Berhasil';
    }

    public function isFailed()
    {
        return $this->status_pembayaran === 'Gagal';
    }
}
