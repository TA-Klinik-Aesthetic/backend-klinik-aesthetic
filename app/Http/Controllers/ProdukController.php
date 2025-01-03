<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index()
    {
        try {
            $produk = Produk::with('kategori')->get();

            return response()->json([
                'message' => 'Data produk berhasil diambil.',
                'data' => $produk,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data produk.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_kategori' => 'required|exists:tb_kategori,id_kategori',
                'nama_produk' => 'required|string|max:255',
                'deskripsi_produk' => 'nullable|string',
                'harga_produk' => 'required|numeric',
                'stok_produk' => 'required|integer',
                'status_produk' => 'required|string|max:255',
                'gambar_produk' => 'required|string|max:255',
            ]);

            $produk = Produk::create($validated);

            return response()->json([
                'message' => 'Produk berhasil ditambahkan.',
                'data' => $produk,
            ], 201);
        } catch (\PDOException $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada koneksi database.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menambahkan produk.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function show($id)
    {
        try {
            $produk = Produk::with('kategori')->findOrFail($id);

            return response()->json([
                'message' => 'Data produk berhasil diambil.',
                'data' => $produk,
            ], 200);
        // } catch (ModelNotFoundException $e) {
        //     return response()->json([
        //         'message' => 'Produk tidak ditemukan.',
        //     ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data produk.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $produk = Produk::findOrFail($id);

            $validated = $request->validate([
                'id_kategori' => 'required|exists:tb_kategori,id_kategori',
                'nama_produk' => 'required|string|max:255',
                'deskripsi_produk' => 'nullable|string',
                'harga_produk' => 'required|numeric',
                'stok_produk' => 'required|integer',
                'status_produk' => 'required|string|max:255',
                'gambar_produk' => 'required|string|max:255',
            ]);

            $produk->update($validated);

            return response()->json([
                'message' => 'Produk berhasil diperbarui.',
                'data' => $produk,
            ], 200);
        // } catch (ModelNotFoundException $e) {
        //     return response()->json([
        //         'message' => 'Produk tidak ditemukan.',
        //     ], 404);
        } catch (\PDOException $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada koneksi database.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui produk.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $produk = Produk::findOrFail($id);

            $produk->delete();

            return response()->json([
                'message' => 'Produk berhasil dihapus.',
            ], 200);
        // } catch (ModelNotFoundException $e) {
        //     return response()->json([
        //         'message' => 'Produk tidak ditemukan.',
        //     ], 404);
        } catch (\PDOException $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada koneksi database.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus produk.',
                'error' => $e->getMessage(),
            ], 500);
        }
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
