<?php

namespace App\Http\Controllers;

use App\Models\DetailPembelianProduk;
use Illuminate\Http\Request;

class DetailPembelianProdukController extends Controller
{
    /**
     * Menampilkan semua detail pembelian produk.
     */
    public function index()
    {
        $details = DetailPembelianProduk::with(['pembelian', 'produk'])->get();

        return response()->json($details, 200);
    }

    /**
     * Menampilkan detail pembelian produk berdasarkan ID.
     */
    public function show($id)
    {
        $detail = DetailPembelianProduk::with(['pembelian', 'produk'])->find($id);

        if (!$detail) {
            return response()->json(['message' => 'Detail pembelian tidak ditemukan'], 404);
        }

        return response()->json($detail, 200);
    }

    /**
     * Menghapus detail pembelian produk.
     */
    public function destroy($id)
    {
        $detail = DetailPembelianProduk::find($id);

        if (!$detail) {
            return response()->json(['message' => 'Detail pembelian tidak ditemukan'], 404);
        }

        $detail->delete();

        return response()->json(['message' => 'Detail pembelian berhasil dihapus'], 200);
    }
}
