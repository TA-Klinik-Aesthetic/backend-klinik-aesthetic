<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'tb_produk'; // Table name
    protected $primaryKey = 'id_produk'; // Primary key

    protected $fillable = [
        'id_kategori',
        'nama_produk',
        'deskripsi_produk',
        'harga_produk',
        'stok_produk',
        'status_produk',
        'gambar_produk',
    ];

    // Relationship: A product belongs to a category
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    // Relasi ke tabel inventaris stok
    // public function inventarisStok()
    // {
    //     return $this->hasMany(InventarisStok::class, 'id_produk', 'id_produk');
    // }

    // // Relasi: Produk milik JenisTreatment
    // public function jenisTreatment()
    // {
    //     return $this->belongsTo(JenisTreatment::class, 'id_jenis_treatment', 'id_jenis_treatment');
    // }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'id_produk', 'id_produk');
    }

    /**
     * Cek apakah produk difavoritkan oleh user tertentu
     */
    public function isFavoritedBy($userId)
    {
        return $this->favorites()->where('id_user', $userId)->exists();
    }

    /**
     * Hitung jumlah favorit untuk produk ini
     */
    public function getFavoritesCountAttribute()
    {
        return $this->favorites()->count();
    }
}
