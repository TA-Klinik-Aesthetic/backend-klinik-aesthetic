<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedbackTreatment extends Model
{
    use HasFactory;

    protected $table = 'tb_feedback_treatment';

    protected $primaryKey = 'id_feedback_treatment';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'id_booking_treatment',
        'rating',
        'teks_feedback',
        'balasan_feedback'
    ];

    public function bookingTreatment()
    {
        return $this->belongsTo(BookingTreatment::class, 'id_booking_treatment', 'id_booking_treatment');
    }
}
