<?php

namespace App\Http\Controllers;

use App\Models\PembelianProduk;
use App\Models\DetailPembelianProduk;
use App\Models\Produk;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class PembelianProdukController extends Controller
{
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'id_user' => 'required',
    //         'produk' => 'required|array',
    //         'produk.*.id_produk' => 'required|exists:tb_produk,id_produk',
    //         'produk.*.jumlah_produk' => 'required|integer|min:1',
    //         'potongan_harga' => 'nullable|numeric|min:0',
    //     ]);

    //     DB::beginTransaction();

    //     try {
    //         $harga_total = 0;
    //         $detail_produk = [];

    //         foreach ($request->produk as $item) {
    //             $produk = Produk::findOrFail($item['id_produk']);

    //             if ($produk->stok_produk < $item['jumlah_produk']) {
    //                 throw new Exception("Stok produk {$produk->nama_produk} tidak mencukupi");
    //             }

    //             $subtotal = $item['jumlah_produk'] * $produk->harga_produk;
    //             $harga_total += $subtotal;

    //             $detail_produk[] = [
    //                 'id_produk' => $item['id_produk'],
    //                 'jumlah_produk' => $item['jumlah_produk'],
    //                 'harga_pembelian_produk' => $produk->harga_produk,
    //             ];

    //             $produk->decrement('stok_produk', $item['jumlah_produk']);
    //         }

    //         $pembelian = PembelianProduk::create([
    //             'id_user' => $request->id_user,
    //             'tgl_pembelian' => now(),
    //             'harga_total' => $harga_total,
    //             'potongan_harga' => $request->potongan_harga ?? 0,
    //             'harga_akhir' => $harga_total - ($request->potongan_harga ?? 0),
    //         ]);

    //         foreach ($detail_produk as $detail) {
    //             DetailPembelianProduk::create([
    //                 'id_pembelian_produk' => $pembelian->id_pembelian_produk,
    //                 'id_produk' => $detail['id_produk'],
    //                 'jumlah_produk' => $detail['jumlah_produk'],
    //                 'harga_pembelian_produk' => $detail['harga_pembelian_produk'],
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Pembelian berhasil disimpan',
    //             'data' => $pembelian,
    //         ]);
    //     } catch (Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //         ], 400);
    //     }
    // }

    public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'required',
            'produk' => 'required|array',
            'produk.*.id_produk' => 'required|exists:tb_produk,id_produk',
            'produk.*.jumlah_produk' => 'required|integer|min:1',
            'id_promo' => 'nullable|exists:tb_promo,id_promo',
        ]);

        DB::beginTransaction();

        try {
            $harga_total = 0;
            $detail_produk = [];

            foreach ($request->produk as $item) {
                $produk = Produk::findOrFail($item['id_produk']);

                if ($produk->stok_produk < $item['jumlah_produk']) {
                    throw new Exception("Stok produk {$produk->nama_produk} tidak mencukupi");
                }

                $subtotal = $item['jumlah_produk'] * $produk->harga_produk;
                $harga_total += $subtotal;

                $detail_produk[] = [
                    'id_produk' => $item['id_produk'],
                    'jumlah_produk' => $item['jumlah_produk'],
                    'harga_penjualan_produk' => $produk->harga_produk,
                ];
            }

            $potongan_harga = 0;
            if ($request->id_promo) {
                $promo = Promo::findOrFail($request->id_promo);
                $potongan_harga = $promo->potongan_harga;
            }

            $pembelian = PembelianProduk::create([
                'id_user' => $request->id_user,
                'tanggal_pembelian' => now(),
                'harga_total' => $harga_total,
                'id_promo' => $request->id_promo,
                'potongan_harga' => $potongan_harga,
                'harga_akhir' => $harga_total - $potongan_harga,
            ]);

            foreach ($detail_produk as $detail) {
                DetailPembelianProduk::create([
                    'id_penjualan_produk' => $pembelian->id_penjualan_produk,
                    'id_produk' => $detail['id_produk'],
                    'jumlah_produk' => $detail['jumlah_produk'],
                    'harga_penjualan_produk' => $detail['harga_penjualan_produk'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penjualan berhasil disimpan',
                'data' => $pembelian,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function index()
    {
        $pembelian = PembelianProduk::with('detailPembelian', 'user')->get();
        return response()->json($pembelian);
    }

    public function show($id)
    {
        $pembelian = PembelianProduk::with('detailPembelian')->find($id);

        if (!$pembelian) {
            return response()->json(['error' => 'Data pembelian tidak ditemukan'], 404);
        }

        return response()->json($pembelian);
    }


    // public function update(Request $request, $id)
    // {
    //     $pembelian = PembelianProduk::find($id);

    //     if (!$pembelian) {
    //         return response()->json(['success' => false, 'message' => 'Data pembelian tidak ditemukan'], 404);
    //     }

    //     $request->validate([
    //         'produk' => 'required|array',
    //         'produk.*.id_produk' => 'required|exists:tb_produk,id_produk',
    //         'produk.*.jumlah_produk' => 'required|integer|min:1',
    //         'potongan_harga' => 'nullable|numeric|min:0',
    //     ]);

    //     DB::beginTransaction();

    //     try {
    //         $harga_total = 0;
    //         $detail_produk = [];

    //         foreach ($pembelian->detailPembelian as $detail) {
    //             $produk = Produk::findOrFail($detail->id_produk);
    //             $produk->increment('stok_produk', $detail->jumlah_produk);
    //         }

    //         $pembelian->detailPembelian()->delete();

    //         foreach ($request->produk as $item) {
    //             $produk = Produk::findOrFail($item['id_produk']);

    //             if ($produk->stok_produk < $item['jumlah_produk']) {
    //                 throw new Exception("Stok produk {$produk->nama_produk} tidak mencukupi");
    //             }

    //             $subtotal = $item['jumlah_produk'] * $produk->harga_produk;
    //             $harga_total += $subtotal;

    //             $detail_produk[] = [
    //                 'id_produk' => $item['id_produk'],
    //                 'jumlah_produk' => $item['jumlah_produk'],
    //                 'harga_pembelian_produk' => $produk->harga_produk,
    //             ];

    //             $produk->decrement('stok_produk', $item['jumlah_produk']);
    //         }

    //         $pembelian->update([
    //             'harga_total' => $harga_total,
    //             'potongan_harga' => $request->potongan_harga ?? $pembelian->potongan_harga,
    //             'harga_akhir' => $harga_total - ($request->potongan_harga ?? $pembelian->potongan_harga),
    //         ]);

    //         foreach ($detail_produk as $detail) {
    //             DetailPembelianProduk::create([
    //                 'id_pembelian_produk' => $pembelian->id_pembelian_produk,
    //                 'id_produk' => $detail['id_produk'],
    //                 'jumlah_produk' => $detail['jumlah_produk'],
    //                 'harga_pembelian_produk' => $detail['harga_pembelian_produk'],
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Data pembelian berhasil diperbarui',
    //             'data' => $pembelian,
    //         ]);
    //     } catch (Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //         ], 400);
    //     }
    // }

    public function update(Request $request, $id)
    {
        $pembelian = PembelianProduk::find($id);

        if (!$pembelian) {
            return response()->json(['success' => false, 'message' => 'Data penjualan tidak ditemukan'], 404);
        }

        $request->validate([
            'produk' => 'required|array',
            'produk.*.id_produk' => 'required|exists:tb_produk,id_produk',
            'produk.*.jumlah_produk' => 'required|integer|min:1',
            'id_promo' => 'nullable|exists:tb_promo,id_promo',
        ]);

        DB::beginTransaction();

        try {
            $harga_total = 0;
            $detail_produk = [];

            foreach ($pembelian->detailPembelian as $detail) {
                $produk = Produk::findOrFail($detail->id_produk);
                $produk->increment('stok_produk', $detail->jumlah_produk);
            }

            $pembelian->detailPembelian()->delete();

            foreach ($request->produk as $item) {
                $produk = Produk::findOrFail($item['id_produk']);

                if ($produk->stok_produk < $item['jumlah_produk']) {
                    throw new Exception("Stok produk {$produk->nama_produk} tidak mencukupi");
                }

                $subtotal = $item['jumlah_produk'] * $produk->harga_produk;
                $harga_total += $subtotal;

                $detail_produk[] = [
                    'id_produk' => $item['id_produk'],
                    'jumlah_produk' => $item['jumlah_produk'],
                    'harga_penjualan_produk' => $produk->harga_produk,
                ];

                $produk->decrement('stok_produk', $item['jumlah_produk']);
            }

            $potongan_harga = $pembelian->potongan_harga;
            if ($request->id_promo) {
                $promo = Promo::findOrFail($request->id_promo);
                $potongan_harga = $promo->potongan_harga;
            }

            $pembelian->update([
                'harga_total' => $harga_total,
                'id_promo' => $request->id_promo,
                'potongan_harga' => $potongan_harga,
                'harga_akhir' => $harga_total - $potongan_harga,
            ]);

            foreach ($detail_produk as $detail) {
                DetailPembelianProduk::create([
                    'id_penjualan_produk' => $pembelian->id_penjualan_produk,
                    'id_produk' => $detail['id_produk'],
                    'jumlah_produk' => $detail['jumlah_produk'],
                    'harga_penjualan_produk' => $detail['harga_penjualan_produk'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data pembelian berhasil diperbarui',
                'data' => $pembelian,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function destroy($id_pembelian_produk)
    {
        $pembelian = PembelianProduk::find($id_pembelian_produk);

        if (!$pembelian) {
            return response()->json(['success' => false, 'message' => 'Data pembelian tidak ditemukan'], 404);
        }

        DB::beginTransaction();

        try {
            foreach ($pembelian->detailPembelian as $detail) {
                $produk = Produk::findOrFail($detail->id_produk);
                $produk->increment('stok_produk', $detail->jumlah_produk);
            }

            $pembelian->detailPembelian()->delete();
            $pembelian->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data pembelian berhasil dihapus',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
