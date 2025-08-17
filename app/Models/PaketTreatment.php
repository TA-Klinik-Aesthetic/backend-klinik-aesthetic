<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaketTreatment extends Model
{
    protected $table = 'tb_paket_treatment';
    protected $primaryKey = 'id_paket_treatment';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nama_paket_treatment',
        'deskripsi_paket_treatment',
        'harga_paket_treatment',
    ];

    protected $casts = [
        'harga_paket_treatment' => 'float',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(DetailPaketTreatment::class, 'id_paket_treatment', 'id_paket_treatment');
    }

    // daftar treatment di dalam paket (via pivot detail paket)
    public function treatments(): BelongsToMany
    {
        return $this->belongsToMany(Treatment::class, 'tb_detail_paket_treatment', 'id_paket_treatment', 'id_treatment')
            ->withPivot('jumlah_penggunaan')
            ->withTimestamps();
    }

    public function pelanggan(): HasMany
    {
        return $this->hasMany(PaketTreatmentPelanggan::class, 'id_paket_treatment', 'id_paket_treatment');
    }
}
