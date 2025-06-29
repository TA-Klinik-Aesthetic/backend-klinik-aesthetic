<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingTreatment extends Model
{
    use HasFactory;

    protected $table = 'tb_booking_treatment';

    protected $primaryKey = 'id_booking_treatment';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'id_user',
        'waktu_treatment',
        'id_dokter',
        'id_beautician',
        'status_booking_treatment',
        'harga_total',
        'id_promo',
        'potongan_harga',
        'besaran_pajak',
        'harga_akhir_treatment',
    ];

    // Relasi ke model User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Relasi ke model Dokter
    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'id_dokter');
    }

    // Relasi ke model Beautician
    public function beautician()
    {
        return $this->belongsTo(Beautician::class, 'id_beautician');
    }

    // Relasi ke model Promo
    public function promo()
    {
        return $this->belongsTo(Promo::class, 'id_promo', 'id_promo');
    }

    public function detailBooking()
    {
        return $this->hasMany(DetailBookingTreatment::class, 'id_booking_treatment', 'id_booking_treatment');
    }

    // Relasi dengan pembayaran treatment
    public function pembayaranTreatment()
    {
        return $this->hasOne(Pembayaran::class, 'id_booking_treatment', 'id_booking_treatment');
    }
}
