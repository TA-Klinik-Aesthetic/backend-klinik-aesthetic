<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Konsultasi extends Model
{
    use HasFactory;

    protected $table = 'tb_konsultasi'; // Nama tabel di database
    protected $fillable = [
        'id_user',
        'id_dokter',
        'id_detail_konsultasi',
        'id_feedback',
        'waktu_konsultasi'
    ];

    // Relasi ke model User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // Relasi ke model Dokter
    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'id_dokter');
    }

    // Relasi ke model DetailKonsultasi
    public function detailKonsultasi()
    {
        return $this->belongsTo(DetailKonsultasi::class, 'id_detail_konsultasi');
    }

    // Relasi ke model Feedback
    public function feedback()
    {
        return $this->belongsTo(Feedback::class, 'id_feedback');
    }
}
