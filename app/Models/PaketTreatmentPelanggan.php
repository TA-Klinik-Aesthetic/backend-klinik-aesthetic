<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaketTreatmentPelanggan extends Model
{
    protected $table = 'tb_paket_treatment_pelanggan';
    protected $primaryKey = 'id_paket_treatment_pelanggan';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_user',
        'id_paket_treatment',
    ];

    protected $casts = [
        'harga_paket_treatment' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(PaketTreatment::class, 'id_paket_treatment', 'id_paket_treatment');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailPaketTreatmentPelanggan::class, 'id_paket_treatment_pelanggan', 'id_paket_treatment_pelanggan');
    }
}
