<?php

namespace App\Http\Controllers;

use App\Models\PembelianProduk;
use App\Models\DetailPembelianProduk;
use App\Models\User;
use App\Models\Produk;
use Illuminate\Http\Request;

class PembelianProdukController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'id_user' => 'required|string|exists:tb_user,id_user',
            'items' => 'required|array|min:1', // Array of products
            'items.*.id_produk' => 'required|exists:tb_produk,id_produk',
            'items.*.jumlah_produk' => 'required|integer|min:1',
            'potongan_harga' => 'required|numeric|min:0',
        ]);

        // Cari user berdasarkan nama
        $user = User::where('id_user', $request->id_user)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Perhitungan harga_total
        $harga_total = 0;

        foreach ($validated['items'] as $item) {
            $produk = Produk::find($item['id_produk']);
            if ($produk) {
                $harga_total += $produk->harga_produk * $item['jumlah_produk'];
            }
        }

        // Perhitungan harga_akhir
        $harga_akhir = $harga_total - $validated['potongan_harga'];

        // Simpan pembelian produk
        $pembelian = PembelianProduk::create([
            'id_user' => $user->id_user,
            'tanggal_pembelian' => now(),
            'harga_total' => $harga_total,
            'potongan_harga' => $validated['potongan_harga'],
            'harga_akhir' => $harga_akhir,
        ]);

        // Simpan detail pembelian
        foreach ($validated['items'] as $item) {
            DetailPembelianProduk::create([
                'id_pembelian_produk' => $pembelian->id_pembelian_produk,
                'id_produk' => $item['id_produk'],
                'jumlah_produk' => $item['jumlah_produk'],
                'harga_pembelian_produk' => Produk::find($item['id_produk'])->harga_produk * $item['jumlah_produk'],
            ]);
        }

        return response()->json([
            'message' => 'Pembelian berhasil disimpan',
            'pembelian' => $pembelian,
        ], 201);
    }
}
