<?php

namespace App\Http\Controllers;

use App\Models\PembelianProduk;
use App\Models\DetailPembelianProduk;
use App\Models\Produk;
use Illuminate\Http\Request;

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
        ]);

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

        $pembelian = PembelianProduk::create([
            'id_user' => $request->id_user,
            'tgl_pembelian' => now(),
            'harga_total' => $harga_total,
            'potongan_harga' => $request->potongan_harga ?? 0,
            'harga_akhir' => $harga_total - ($request->potongan_harga ?? 0),
        ]);

        foreach ($detail_produk as $detail) {
            DetailPembelianProduk::create([
                'id_pembelian_produk' => $pembelian->id_pembelian_produk,
                'id_produk' => $detail['id_produk'],
                'jumlah_produk' => $detail['jumlah_produk'],
                'harga_pembelian_produk' => $detail['harga_pembelian_produk'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembelian berhasil disimpan',
            'data' => $pembelian,
        ]);
    }
}
