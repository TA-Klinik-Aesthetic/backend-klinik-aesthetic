<?php

namespace App\Http\Controllers;

use App\Models\PembayaranProduk;
use App\Models\PembelianProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranProdukController extends Controller
{
    // Menampilkan semua pembayaran produk
    public function index()
    {
        $pembayaranProdukList = PembayaranProduk::with('penjualanProduk')->get();
        return response()->json($pembayaranProdukList);
    }

    // Menyimpan pembayaran produk baru
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validasi input pembayaran produk
            $validatedPayment = $request->validate([
                'id_penjualan_produk' => 'required|exists:tb_penjualan_produk,id_penjualan_produk',
                'metode_pembayaran' => 'required|string',
            ]);

            // Ambil data penjualan produk berdasarkan id_penjualan_produk
            $penjualanProduk = PembelianProduk::findOrFail($validatedPayment['id_penjualan_produk']);
            $hargaAkhirProduk = $penjualanProduk->harga_akhir;

            // Menyimpan pembayaran produk
            $pembayaranProduk = PembayaranProduk::create([
                'id_penjualan_produk' => $validatedPayment['id_penjualan_produk'],
                'metode_pembayaran' => $validatedPayment['metode_pembayaran'],
                'harga_akhir' => $hargaAkhirProduk
            ]);

            DB::commit();

            return response()->json([
                'pembayaran_produk' => $pembayaranProduk,
                'message' => 'Pembayaran produk berhasil disimpan'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error while creating pembayaran produk',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Memperbarui pembayaran produk yang sudah ada
    public function update(Request $request, $id)
    {
        $request->validate([
            'uang' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();

        try {
            $pembayaranProduk = PembayaranProduk::findOrFail($id);
            $hargaAkhirProduk = $pembayaranProduk->penjualanProduk->harga_akhir;

            $pembayaranProduk->uang = $request->uang;

            // Menghitung kembalian
            $pembayaranProduk->kembalian = $request->uang - $hargaAkhirProduk;
            $pembayaranProduk->save();

            // Memperbarui status pembayaran pada tb_penjualan_produk menjadi "Sudah Dibayar"
            $penjualanProduk = $pembayaranProduk->penjualanProduk;
            $penjualanProduk->status_pembayaran = 'Sudah Dibayar';
            $penjualanProduk->save();

            DB::commit();

            return response()->json([
                'pembayaran_produk' => $pembayaranProduk,
                'message' => 'Pembayaran produk berhasil diperbarui'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error while updating pembayaran produk',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
