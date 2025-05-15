<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KompensasiDiberikan extends Model
{
    use HasFactory;

    protected $table = 'tb_kompensasi_diberikan';
    protected $primaryKey = 'id_kompensasi_diberikan';
    protected $fillable = [
        'id_komplain', 
        'id_kompensasi', 
        'kode_kompensasi', 
        'tanggal_berakhir_kompensasi', 
        'tanggal_pemakaian_kompensasi', 
        'status_kompensasi'
    ];

    public function komplain()
    {
        return $this->belongsTo(Komplain::class, 'id_komplain');
    }

    public function kompensasi()
    {
        return $this->belongsTo(Kompensasi::class, 'id_kompensasi');
    }

    public function detailBookingTreatment()
    {
        return $this->hasMany(DetailBookingTreatment::class, 'id_kompensasi_diberikan');
    }
}
