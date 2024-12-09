<?php

// app/Http/Controllers/PembelianProdukController.php
namespace App\Http\Controllers;

use App\Models\PembelianProduk;
use App\Models\DetailPembelianProduk;
use Illuminate\Http\Request;

class PembelianProdukController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'required|exists:tb_user,id',
            'details' => 'required|array',
            'details.*.produk_id' => 'required|exists:produk,id',
            'details.*.jumlah_produk' => 'required|integer|min:1',
        ]);

        $details = $request->details;

        $harga_total = 0;
        foreach ($details as $detail) {
            $produk = Produk::find($detail['id_produk']);
            $harga_total += $detail['jumlah_produk'] * $produk->harga;
        }

        $potongan_harga = $request->potongan_harga ?? 0;
        $harga_akhir = $harga_total - $potongan_harga;

        $pembelian = PembelianProduk::create([
            'id_user' => $request->id_user,
            'harga_total' => $harga_total,
            'potongan_harga' => $potongan_harga,
            'harga_akhir' => $harga_akhir,
        ]);

        foreach ($details as $detail) {
            TbDetailPembelianProduk::create([
                'pembelian_produk_id' => $pembelian->id,
                'produk_id' => $detail['produk_id'],
                'jumlah_produk' => $detail['jumlah_produk'],
                'harga_pembelian_produk' => $detail['jumlah_produk'] * Produk::find($detail['produk_id'])->harga,
            ]);
        }

        return response()->json(['message' => 'Pembelian berhasil dibuat', 'data' => $pembelian], 201);
    }
}
