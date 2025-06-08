<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailBookingTreatment extends Model
{
    use HasFactory;

    protected $table = 'tb_detail_booking_treatment'; // Nama tabel di database

    protected $primaryKey = 'id_detail_booking_treatment';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'id_booking_treatment',
        'id_treatment',
        'biaya_treatment',
        'id_kompensasi_diberikan'
    ];

    // Relasi ke model treatment
    public function treatment()
    {
        return $this->belongsTo(Treatment::class, 'id_treatment');
    }

    public function booking()
    {
        return $this->belongsTo(BookingTreatment::class, 'id_booking_treatment');
    }

    public function kompensasiDiberikan()
    {
        return $this->belongsTo(KompensasiDiberikan::class, 'id_kompensasi_diberikan');
    }
    
    // public function detailProduk()
    // {
    //     return $this->hasMany(DetailBookingProduk::class, 'id_detail_booking_treatment', 'id_detail_booking_treatment');
    // }
}
