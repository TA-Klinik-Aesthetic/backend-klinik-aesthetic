<?php

namespace App\Http\Controllers;

use App\Models\PembelianProduk;
use App\Models\DetailPembelianProduk;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembelianProdukController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'required',
            'produk' => 'required|array',
            'produk.*.id_produk' => 'required|exists:tb_produk,id_produk',
            'produk.*.jumlah_produk' => 'required|integer|min:1',
            'potongan_harga' => 'nullable|numeric|min:0',
        ], [
            'id_user.required' => 'ID pengguna harus diisi',
            'produk.required' => 'Produk tidak boleh kosong',
            'produk.*.id_produk.exists' => 'Produk tidak ditemukan',
            'produk.*.jumlah_produk.min' => 'Jumlah produk minimal 1',
        ]);

        DB::beginTransaction();

        try {
            $harga_total = 0;
            $detail_produk = [];

            foreach ($request->produk as $item) {
                $produk = Produk::find($item['id_produk']);

                if ($produk->stok_produk < $item['jumlah_produk']) {
                    return response()->json(['error' => 'Stok produk tidak mencukupi'], 400);
                }

                $subtotal = $item['jumlah_produk'] * $produk->harga_produk;
                $harga_total += $subtotal;

                $detail_produk[] = [
                    'id_produk' => $item['id_produk'],
                    'jumlah_produk' => $item['jumlah_produk'],
                    'harga_pembelian_produk' => $produk->harga_produk,
                ];

                $produk->decrement('stok_produk', $item['jumlah_produk']);
            }

            $potongan_harga = $request->potongan_harga ?? 0;

            $pembelian = PembelianProduk::create([
                'id_user' => $request->id_user,
                'tgl_pembelian' => now(),
                'harga_total' => $harga_total,
                'potongan_harga' => $potongan_harga,
                'harga_akhir' => $harga_total - $potongan_harga,
            ]);

            foreach ($detail_produk as $detail) {
                DetailPembelianProduk::create([
                    'id_pembelian_produk' => $pembelian->id_pembelian_produk,
                    'id_produk' => $detail['id_produk'],
                    'jumlah_produk' => $detail['jumlah_produk'],
                    'harga_pembelian_produk' => $detail['harga_pembelian_produk'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembelian berhasil disimpan',
                'data' => $pembelian,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan data pembelian: ' . $e->getMessage()], 500);
        }
    }

    public function index()
    {
        $pembelian = PembelianProduk::with('detailPembelian')->get();
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

    public function update(Request $request, $id)
    {
        $pembelian = PembelianProduk::find($id);

        if (!$pembelian) {
            return response()->json(['error' => 'Data pembelian tidak ditemukan'], 404);
        }

        $request->validate([
            'produk' => 'required|array',
            'produk.*.id_produk' => 'required|exists:tb_produk,id_produk',
            'produk.*.jumlah_produk' => 'required|integer|min:1',
            'potongan_harga' => 'nullable|numeric|min:0',
        ], [
            'produk.required' => 'Produk tidak boleh kosong',
            'produk.*.id_produk.exists' => 'Produk tidak ditemukan',
            'produk.*.jumlah_produk.min' => 'Jumlah produk minimal 1',
        ]);

        DB::beginTransaction();

        try {
            $harga_total = 0;
            $detail_produk = [];

            // Kembalikan stok produk sebelumnya
            foreach ($pembelian->detailPembelian as $detail) {
                $produk = Produk::find($detail->id_produk);
                $produk->increment('stok_produk', $detail->jumlah_produk);
            }

            // Hapus detail pembelian lama
            $pembelian->detailPembelian()->delete();

            foreach ($request->produk as $item) {
                $produk = Produk::find($item['id_produk']);

                if ($produk->stok_produk < $item['jumlah_produk']) {
                    throw new \Exception('Stok produk tidak mencukupi');
                }

                $subtotal = $item['jumlah_produk'] * $produk->harga_produk;
                $harga_total += $subtotal;

                $detail_produk[] = [
                    'id_produk' => $item['id_produk'],
                    'jumlah_produk' => $item['jumlah_produk'],
                    'harga_pembelian_produk' => $produk->harga_produk,
                ];

                $produk->decrement('stok_produk', $item['jumlah_produk']);
            }

            $potongan_harga = $request->potongan_harga ?? $pembelian->potongan_harga;

            $pembelian->update([
                'harga_total' => $harga_total,
                'potongan_harga' => $potongan_harga,
                'harga_akhir' => $harga_total - $potongan_harga,
            ]);

            foreach ($detail_produk as $detail) {
                DetailPembelianProduk::create([
                    'id_pembelian_produk' => $pembelian->id_pembelian_produk,
                    'id_produk' => $detail['id_produk'],
                    'jumlah_produk' => $detail['jumlah_produk'],
                    'harga_pembelian_produk' => $detail['harga_pembelian_produk'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data pembelian berhasil diperbarui',
                'data' => $pembelian,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Terjadi kesalahan saat memperbarui data pembelian: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id_pembelian_produk)
    {
        $pembelian = PembelianProduk::find($id_pembelian_produk);

        if (!$pembelian) {
            return response()->json(['error' => 'Data pembelian tidak ditemukan'], 404);
        }

        DB::beginTransaction();

        try {
            // Kembalikan stok produk sebelumnya
            foreach ($pembelian->detailPembelian as $detail) {
                $produk = Produk::find($detail->id_produk);
                $produk->increment('stok_produk', $detail->jumlah_produk);
            }

            // Hapus semua detail pembelian terkait
            $pembelian->detailPembelian()->delete();

            // Hapus data pembelian
            $pembelian->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data pembelian berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Terjadi kesalahan saat menghapus data pembelian: ' . $e->getMessage()], 500);
        }
    }
}
