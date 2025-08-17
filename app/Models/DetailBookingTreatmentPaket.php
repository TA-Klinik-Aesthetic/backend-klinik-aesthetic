<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailBookingTreatmentPaket extends Model
{
    protected $table = 'tb_detail_booking_treatment_paket';
    protected $primaryKey = 'id_detail_booking_treatment_paket';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_booking_treatment_paket',
        'id_paket_treatment_pelanggan',
        'id_treatment',
        'jumlah_dipakai',
    ];

    protected $casts = [
        'jumlah_dipakai' => 'int',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(BookingTreatmentPaket::class, 'id_booking_treatment_paket', 'id_booking_treatment_paket');
    }

    public function paketPelanggan(): BelongsTo
    {
        return $this->belongsTo(PaketTreatmentPelanggan::class, 'id_paket_treatment_pelanggan', 'id_paket_treatment_pelanggan');
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class, 'id_treatment', 'id_treatment');
    }
}
