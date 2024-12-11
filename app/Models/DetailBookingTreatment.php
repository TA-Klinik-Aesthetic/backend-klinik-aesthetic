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
        'id_dokter',
        'id_beautician',
    ];

    // Relasi ke model treatment
    public function treatment()
    {
        return $this->belongsTo(Treatment::class, 'id_treatment');
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

    public function booking()
    {
        return $this->belongsTo(BookingTreatment::class, 'id_booking');
    }
}
