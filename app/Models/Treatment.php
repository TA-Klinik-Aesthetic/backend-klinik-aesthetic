<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Treatment extends Model
{
    use HasFactory;

    protected $table = 'tb_treatment';

    protected $primaryKey = 'id_treatment';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'id_jenis_treatment',
        'nama_treatment',
        'deskripsi_treatment',
        'biaya_treatment',
        'estimasi_treatment',
        'gambar_treatment'
    ];

    // Relasi ke model Jenis Treatment
     public function jenis_treatment()
     {
        return $this->belongsTo(JenisTreatment::class, 'id_jenis_treatment', 'id_jenis_treatment');
    }

    // Relasi ke model detail booking Treatment
    public function detail_booking_treatment()
    {
        return $this->hasMany(DetailBookingTreatment::class, 'id_treatment');
    }

    public function detailKonsultasi()
    {
        return $this->hasMany(DetailKonsultasi::class, 'id_treatment');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'id_treatment', 'id_treatment');
    }

    /**
     * Cek apakah treatment difavoritkan oleh user tertentu
     */
    public function isFavoritedBy($userId)
    {
        return $this->favorites()->where('id_user', $userId)->exists();
    }

    /**
     * Hitung jumlah favorit untuk treatment ini
     */
    public function getFavoritesCountAttribute()
    {
        return $this->favorites()->count();
    }
}
