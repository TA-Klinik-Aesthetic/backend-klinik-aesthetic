<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'tb_kategori'; // Table name

    protected $fillable = [
        'nama_kategori',
        'deskripsi_kategori',
    ];

    // Relationship: A category has many products
    public function produk()
    {
        return $this->hasMany(Produk::class, 'id_kategori');
    }
}
