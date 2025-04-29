<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class DetailBookingProduk extends Model
// {
//     use HasFactory;

//     protected $table = 'tb_detail_booking_produk';
//     protected $primaryKey = 'id_detail_booking_produk';

//     protected $fillable = [
//         'id_detail_booking_treatment',
//         'id_produk',
//         'jumlah_produk',
//         'harga_produk',
//         'harga_total_produk'
//     ];

//     // Relasi ke DetailBookingTreatment
//     public function detailBookingTreatment()
//     {
//         return $this->belongsTo(DetailBookingTreatment::class, 'id_detail_booking_treatment', 'id_detail_booking_treatment');
//     }

//     // Relasi ke Produk
//     public function produk()
//     {
//         return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
//     }
// }
