<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranTreatment extends Model
{
    use HasFactory;

    protected $table = 'tb_pembayaran_treatment';

    protected $primaryKey = 'id_pembayaran_treatment';

    protected $fillable = [
        'id_booking_treatment',
        'metode_pembayaran',
        'harga_akhir_treatment',
        'pajak',
        'total',
        'uang',
        'kembalian',
    ];

    protected $casts = [
        'harga_akhir_treatment' => 'decimal:2',
        'total_bayar' => 'decimal:2',
        'kembalian' => 'decimal:2',
    ];

    // Relasi dengan BookingTreatment
    public function bookingTreatment()
    {
        return $this->belongsTo(BookingTreatment::class, 'id_booking_treatment');
    }
    
}
