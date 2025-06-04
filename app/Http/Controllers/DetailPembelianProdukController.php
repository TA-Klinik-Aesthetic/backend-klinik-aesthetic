<?php

namespace App\Http\Controllers;

use App\Models\DetailPembelianProduk;
use App\Models\Produk;
use App\Models\PembelianProduk;
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

        // Ambil data yang dibutuhkan sebelum delete
        $jumlahProduk = $detail->jumlah_produk;
        $idPenjualan = $detail->id_penjualan_produk;

        // Kurangi harga_total dari penjualan produk
        $penjualan = PembelianProduk::find($idPenjualan);
        if ($penjualan) {
            $subtotal = $jumlahProduk * $detail->harga_penjualan_produk; // pastikan kolom ini sesuai migrasi
            $penjualan->harga_total -= $subtotal;
            if ($penjualan->harga_total < 0) {
                $penjualan->harga_total = 0;
            }
            // Hitung ulang harga_akhir
            $penjualan->harga_akhir = max(0, $penjualan->harga_total - $penjualan->potongan_harga);
            $penjualan->save();
        }

        $detail->delete();

        return response()->json(['message' => 'Detail pembelian berhasil dihapus'], 200);
    }
}
