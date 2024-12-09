<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index()
    {
        $produk = Produk::with('kategori')->get();
        return response()->json($produk);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_kategori' => 'required|exists:tb_kategori,id_kategori',
            'nama_produk' => 'required|string|max:255',
            'deskripsi_produk' => 'nullable|string',
            'harga_produk' => 'required|numeric',
            'stok_produk' => 'required|integer',
            'status_produk' => '',
        ]);

        $produk = Produk::create($validated);

        return response()->json($produk, 201);
    }

    public function show($id)
    {
        $produk = Produk::with('kategori')->findOrFail($id);
        return response()->json($produk);
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $validated = $request->validate([
            'id_kategori' => 'required|exists:tb_kategori,id_kategori',
            'nama_produk' => 'required|string|max:255',
            'deskripsi_produk' => 'nullable|string',
            'harga_produk' => 'required|numeric',
            'stok_produk' => 'required|integer',
        ]);

        $produk->update($validated);

        return response()->json($produk);
    }

    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);
        $produk->delete();

        return response()->json(null, 204);
    }

    public function getProdukByKategori($id_kategori)
    {
        // Ambil data produk berdasarkan id_kategori
        $produk = Produk::where('id_kategori', $id_kategori)->get();

        // Periksa apakah data produk ditemukan
        if ($produk->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada produk untuk kategori ini.',
                'data' => [],
            ], 404);
        }

        // Kembalikan data produk dalam format JSON
        return response()->json([
            'message' => 'Produk ditemukan.',
            'data' => $produk,
        ], 200);
    }
}
