<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\PembelianProduk;
use App\Models\Produk;
use App\Models\InventarisStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranProdukController extends Controller
{
    public function index()
    {
        // Ambil pembayaran (model Pembayaran) yang id_penjualan_produk-nya terisi
        $list = Pembayaran::with('penjualanProduk.user')
            ->whereNotNull('id_penjualan_produk')
            ->get();

        return response()->json($list);
    }

    // Menyimpan pembayaran produk baru
    // public function store(Request $request)
    // {
    //     DB::beginTransaction();

    //     try {
    //         // Validasi input pembayaran produk
    //         $validatedPayment = $request->validate([
    //             'id_penjualan_produk' => 'required|exists:tb_penjualan_produk,id_penjualan_produk',
    //             'metode_pembayaran' => 'required|string',
    //         ]);

    //         // Ambil data penjualan produk berdasarkan id_penjualan_produk
    //         $penjualanProduk = PembelianProduk::findOrFail($validatedPayment['id_penjualan_produk']);
    //         $hargaAkhirProduk = $penjualanProduk->harga_akhir;

    //         // Menyimpan pembayaran produk
    //         $pembayaranProduk = PembayaranProduk::create([
    //             'id_penjualan_produk' => $validatedPayment['id_penjualan_produk'],
    //             'metode_pembayaran' => $validatedPayment['metode_pembayaran'],
    //             'harga_akhir' => $hargaAkhirProduk
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'pembayaran_produk' => $pembayaranProduk,
    //             'message' => 'Pembayaran produk berhasil disimpan'
    //         ], 201);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Error while creating pembayaran produk',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function show($id)
    {
        // Mencari data pembayaran treatment berdasarkan ID
        $pembayaran = Pembayaran::with('penjualanProduk.user', 'penjualanProduk.detailPembelian')->find($id);

        // Jika data tidak ditemukan, kembalikan respons error
        if (!$pembayaran) {
            return response()->json([
                'message' => 'Pembayaran Treatment tidak ditemukan',
            ], 404);
        }

        // Jika ini bukan pembayaran treatment
        if (is_null($pembayaran->id_penjualan_produk)) {
            return response()->json([
                'message' => 'Pembayaran ini bukan pembayaran produk',
            ], 400);
        }

        // Mengembalikan data pembayaran treatment
        return response()->json([
            'message' => 'Data Pembayaran Treatment ditemukan',
            'data' => $pembayaran
        ]);
    }

    // Memperbarui pembayaran produk yang sudah ada
    public function update(Request $request, $id)
    {
        try {

            $request->validate([
                'metode_pembayaran' => 'required|string|in:Tunai,Non Tunai',
                'uang'               => 'required|numeric|min:0',
            ]);

            DB::beginTransaction();
            // ambil baris pembayaran
            $pembayaran = Pembayaran::findOrFail($id);

            // Hanya dari penjualan produk
            $hargaAkhir = $pembayaran->penjualanProduk->harga_akhir;

            // update pembayaran
            $pembayaran->uang              = $request->uang;
            $pembayaran->kembalian         = $request->uang - $hargaAkhir;
            $pembayaran->metode_pembayaran = $request->metode_pembayaran;
            $pembayaran->status_pembayaran = 'Sudah Dibayar';
            $pembayaran->waktu_pembayaran  = now();
            $pembayaran->save();

            DB::commit();

            return response()->json([
                'pembayaran_produk' => $pembayaran,
                'message'           => 'Pembayaran produk berhasil diperbarui',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error while updating pembayaran produk',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
