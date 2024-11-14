<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'tb_feedback'; // Nama tabel di database
    protected $fillable = [
        'id_user',
        'rating',
        'teks_feedback',
        'balasan_feedback'
    ];

    // Relasi ke model User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // Relasi ke model Konsultasi
    public function konsultasi()
    {
        return $this->hasMany(Konsultasi::class, 'id_feedback');
    }
}
