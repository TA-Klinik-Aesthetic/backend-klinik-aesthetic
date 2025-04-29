<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Komplain extends Model
{
    use HasFactory;

    protected $table = 'tb_komplain';

    protected $primaryKey = 'id_komplain';

    public $timestamps = true;

    protected $fillable = [
        'id_user', 
        'teks_komplain', 
        'gambar_komplain', 
        'gambar_bukti_transaksi', 
        'balasan_komplain', 
        'id_kompensasi', 
        'tanggal_berakhir_kompensasi', 
        'status_kompensasi'
    ];

    public function kompensasi()
    {
        return $this->belongsTo(Kompensasi::class, 'id_kompensasi');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
