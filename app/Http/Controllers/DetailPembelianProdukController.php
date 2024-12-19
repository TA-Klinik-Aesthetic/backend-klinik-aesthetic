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
        try {
            $details = DetailPembelianProduk::with(['pembelian', 'produk'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Data detail pembelian berhasil diambil',
                'data' => $details,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan detail pembelian produk berdasarkan ID.
     */
    public function show($id)
    {
        try {
            $detail = DetailPembelianProduk::with(['pembelian', 'produk'])->find($id);

            if (!$detail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detail pembelian tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail pembelian berhasil ditemukan',
                'data' => $detail,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menghapus detail pembelian produk.
     */
    public function destroy($id)
    {
        try {
            $detail = DetailPembelianProduk::find($id);

            if (!$detail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detail pembelian tidak ditemukan',
                ], 404);
            }

            $detail->delete();

            return response()->json([
                'success' => true,
                'message' => 'Detail pembelian berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
