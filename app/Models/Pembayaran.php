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
        'id_penjualan_paket_treatment',
        'uang',
        'kembalian',
        'metode_pembayaran',
        'status_pembayaran',
        'waktu_pembayaran',
        'gambar_bukti_pembayaran',
        'snap_token',
        'snap_url',
        'order_id',
        'transaction_id',
        'transaction_status',
        'payment_type',
        'va_number',
        'bank',
        'gross_amount',
        'payment_details',
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

    // TAMBAH: Constants untuk status pembayaran yang HILANG
    const STATUS_BELUM_DIBAYAR = 'Belum Dibayar';
    const STATUS_PENDING = 'Pending';
    const STATUS_BERHASIL = 'Berhasil';
    const STATUS_GAGAL = 'Gagal';
    const STATUS_SUDAH_DIBAYAR = 'Sudah Dibayar';
    const STATUS_MENUNGGU_PEMBAYARAN = 'Menunggu Pembayaran';
    const STATUS_DIBATALKAN = 'Dibatalkan';
    const STATUS_EXPIRED = 'Expired';
    const STATUS_REFUND = 'Refund';

    // Constants untuk transaction status Midtrans
    const MIDTRANS_CAPTURE = 'capture';
    const MIDTRANS_SETTLEMENT = 'settlement';  // INI YANG BERHASIL
    const MIDTRANS_PENDING = 'pending';
    const MIDTRANS_DENY = 'deny';
    const MIDTRANS_EXPIRE = 'expire';
    const MIDTRANS_CANCEL = 'cancel';
    const MIDTRANS_REFUND = 'refund';
    const MIDTRANS_PARTIAL_REFUND = 'partial_refund';
    const MIDTRANS_FAILURE = 'failure';

    // Helper methods untuk status
    public function isMidtransSuccess()
    {
        return in_array($this->transaction_status, [
            self::MIDTRANS_SETTLEMENT,
            self::MIDTRANS_CAPTURE
        ]);
    }

    public function isMidtransFailure()
    {
        return in_array($this->transaction_status, [
            self::MIDTRANS_DENY,
            self::MIDTRANS_EXPIRE,
            self::MIDTRANS_CANCEL,
            self::MIDTRANS_FAILURE
        ]);
    }

    public function isPending()
    {
        return in_array($this->status_pembayaran, [
            self::STATUS_PENDING,
            self::STATUS_MENUNGGU_PEMBAYARAN
        ]);
    }

    public function isSuccess()
    {
        return in_array($this->status_pembayaran, [
            self::STATUS_BERHASIL,
            self::STATUS_SUDAH_DIBAYAR
        ]);
    }

    public function isFailed()
    {
        return in_array($this->status_pembayaran, [
            self::STATUS_GAGAL,
            self::STATUS_DIBATALKAN,
            self::STATUS_EXPIRED
        ]);
    }

    /**
     * TAMBAHAN: Cek apakah status adalah final (tidak akan berubah lagi)
     */
    public function isFinalStatus()
    {
        return in_array($this->status_pembayaran, [
            self::STATUS_BERHASIL,
            self::STATUS_SUDAH_DIBAYAR,
            self::STATUS_GAGAL,
            self::STATUS_DIBATALKAN,
            self::STATUS_EXPIRED,
            self::STATUS_REFUND
        ]);
    }

    /**
     * TAMBAHAN: Cek apakah pembayaran masih bisa di-sync
     */
    public function canBeSync()
    {
        return $this->order_id &&
               !$this->isFinalStatus() &&
               $this->created_at->diffInHours(now()) <= 48; // Sync dalam 48 jam
    }

    // Relasi
    public function penjualanProduk()
    {
        return $this->belongsTo(PembelianProduk::class, 'id_penjualan_produk', 'id_penjualan_produk');
    }

    public function bookingTreatment()
    {
        return $this->belongsTo(BookingTreatment::class, 'id_booking_treatment', 'id_booking_treatment');
    }

    public function penjualanPaketTreatment()
    {
        return $this->belongsTo(PenjualanPaketTreatment::class, 'id_penjualan_paket_treatment', 'id_penjualan_paket_treatment');
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
