<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'tb_feedback_konsultasi'; 

    protected $primaryKey = 'id_feedback_konsultasi'; 
    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'id_konsultasi',
        'rating',
        'teks_feedback',
        'balasan_feedback'
    ];

    // Relasi ke model User
    public function konsultasi()
    {
        return $this->belongsTo(Konsultasi::class, 'id_konsultasi', 'id_konsultasi');
    }
}
