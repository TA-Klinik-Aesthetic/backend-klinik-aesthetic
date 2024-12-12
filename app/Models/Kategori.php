<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'tb_kategori'; // Table name
    protected $primaryKey = 'id_kategori'; // Nama kolom primary key
    public $timestamps = false; // Menonaktifkan timestamps

    protected $fillable = [
        'nama_kategori',
    ];

    // Relationship: A category has many products
    public function produk()
    {
        return $this->hasMany(Produk::class, 'id_kategori', 'id_kategori');
    }
}
