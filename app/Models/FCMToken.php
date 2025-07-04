<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FcmToken extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'tb_fcm_token';

    /**
     * Kunci utama tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_fcm_token';

    /**
     * Atribut yang dapat diisi.
     *
     * @var array
     */
    protected $fillable = [
        'id_user',
        'device_token',
        'device_type',
        'is_active',
    ];

    /**
     * Atribut yang harus dikonversi ke tipe data tertentu.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relasi dengan user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
