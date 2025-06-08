<?php

namespace App\Http\Controllers;

use App\Models\InventarisStok;
use Illuminate\Http\Request;

class InventarisStokController extends Controller
{
    /**
     * Menampilkan seluruh data inventaris stok.
     */
    public function index()
    {
        $data = InventarisStok::with('produk:id_produk,nama_produk')
            ->orderBy('waktu_perubahan', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data inventaris stok berhasil diambil',
            'data' => $data
        ]);
    }
}
