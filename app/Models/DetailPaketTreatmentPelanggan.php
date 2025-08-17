<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPaketTreatmentPelanggan extends Model
{
    protected $table = 'tb_detail_paket_treatment_pelanggan';
    protected $primaryKey = 'id_detail_paket_treatment_pelanggan';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_paket_treatment_pelanggan',
        'id_treatment',
        'jumlah_penggunaan',
        'jumlah_terpakai',
    ];

    protected $casts = [
        'jumlah_penggunaan' => 'int',
        'jumlah_terpakai'   => 'int',
    ];

    protected $appends = ['sisa_penggunaan'];

    public function paketPelanggan(): BelongsTo
    {
        return $this->belongsTo(PaketTreatmentPelanggan::class, 'id_paket_treatment_pelanggan', 'id_paket_treatment_pelanggan');
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class, 'id_treatment', 'id_treatment');
    }

    public function getSisaPenggunaanAttribute(): int
    {
        return max(0, (int)$this->jumlah_penggunaan - (int)$this->jumlah_terpakai);
    }
}
