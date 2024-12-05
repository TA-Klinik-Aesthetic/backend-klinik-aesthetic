<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingTreatment extends Model
{
    use HasFactory;

    protected $table = 'tb_booking_treatment'; // Nama tabel di database
    protected $fillable = [
        'id_user',
        'waktu_treatment',
        'status_booking_treatment',
    ];

    // Relasi ke model User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
