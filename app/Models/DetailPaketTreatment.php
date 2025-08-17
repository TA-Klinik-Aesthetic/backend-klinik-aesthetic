<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPaketTreatment extends Model
{
    protected $table = 'tb_detail_paket_treatment';
    protected $primaryKey = 'id_detail_paket_treatment';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_paket_treatment',
        'id_treatment',
        'jumlah_penggunaan',
    ];

    protected $casts = [
        'jumlah_penggunaan' => 'int',
    ];

    public function paket(): BelongsTo
    {
        return $this->belongsTo(PaketTreatment::class, 'id_paket_treatment', 'id_paket_treatment');
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class, 'id_treatment', 'id_treatment');
    }
}
