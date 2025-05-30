<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class KomplainTreatment extends Model
// {
//     use HasFactory;

//     protected $table = 'tb_komplain_treatment'; // Nama tabel

//     protected $fillable = [
//         'id_komplain', 
//         'id_detail_booking_treatment',
//     ];

//     // Relasi ke Komplain
//     public function komplain()
//     {
//         return $this->belongsTo(Komplain::class, 'id_komplain');
//     }

//     // Relasi ke DetailBookingTreatment
//     public function detailBookingTreatment()
//     {
//         return $this->belongsTo(DetailBookingTreatment::class, 'id_detail_booking_treatment');
//     }
// }
