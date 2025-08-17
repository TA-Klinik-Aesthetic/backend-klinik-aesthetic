<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookingTreatmentPaket extends Model
{
    use HasFactory;
    
    protected $table = 'tb_booking_treatment_paket';
    protected $primaryKey = 'id_booking_treatment_paket';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_user',
        'waktu_treatment',
        'id_dokter',
        'id_beautician',
        'status_booking_treatment',
        'treatment_mulai',
        'treatment_selesai',
    ];

    protected $casts = [
        'waktu_treatment'  => 'datetime',
        'treatment_mulai'  => 'datetime',
        'treatment_selesai'=> 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'id_dokter', 'id_dokter');
    }

    public function beautician(): BelongsTo
    {
        return $this->belongsTo(Beautician::class, 'id_beautician', 'id_beautician');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailBookingTreatmentPaket::class, 'id_booking_treatment_paket', 'id_booking_treatment_paket');
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
