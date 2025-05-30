<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Komplain extends Model
{
    use HasFactory;

    protected $table = 'tb_komplain';

    protected $primaryKey = 'id_komplain';

    public $timestamps = true;

    protected $fillable = [
        'id_user',
        'teks_komplain',
        'id_booking_treatment',
        'id_detail_booking_treatment',
        'gambar_komplain',
        'balasan_komplain',
        'pemberian_kompensasi',
    ];

    public function kompensasiDiberikan()
    {
        return $this->hasMany(KompensasiDiberikan::class, 'id_komplain');
    }

    // Relasi satu ke banyak dengan KomplainTreatment
    // public function komplainTreatments()
    // {
    //     return $this->hasMany(KomplainTreatment::class, 'id_komplain');
    // }

    // Relasi ke model BookingTreatment (many-to-one)
    public function bookingTreatment()
    {
        return $this->belongsTo(BookingTreatment::class, 'id_booking_treatment');
    }

    // Relasi ke DetailBookingTreatment
    public function detailBookingTreatment()
    {
        return $this->belongsTo(DetailBookingTreatment::class, 'id_detail_booking_treatment');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
