<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'tb_notifikasi';

    /**
     * Kunci utama tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_notifikasi';

    /**
     * Atribut yang dapat diisi.
     *
     * @var array
     */
    protected $fillable = [
        'id_user',
        'judul',
        'pesan',
        'jenis',
        'id_referensi',
        'status',
        'gambar',
        'tanggal_notifikasi',
    ];

    /**
     * Atribut yang harus dikonversi ke tipe data tertentu.
     *
     * @var array
     */
    protected $casts = [
        'tanggal_notifikasi' => 'datetime',
    ];

    /**
     * Relasi dengan user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
