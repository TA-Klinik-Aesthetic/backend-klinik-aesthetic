<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = Kategori::all();
        return response()->json($kategori);
    }

    public function store(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'nama_kategori' => 'required|string|max:255',
            ]);

            // Buat kategori baru
            $kategori = Kategori::create($validated);

            // Berikan respons sukses
            return response()->json([
                'message' => 'Kategori berhasil dibuat.',
                'data' => $kategori,
            ], 200);
        } catch (\PDOException $e) {
            // Penanganan error koneksi database
            return response()->json([
                'message' => 'Terjadi kesalahan pada koneksi database.',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            // Penanganan error umum
            return response()->json([
                'message' => 'Gagal menyimpan kategori.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        try {
            $kategori = Kategori::findOrFail($id);
            return response()->json($kategori, 200);
        // } catch (ModelNotFoundException $e) {
        //     return response()->json(['message' => 'Kategori tidak ditemukan.'], 404);
        } catch (\PDOException $e) {
            return response()->json(['message' => 'Kesalahan pada koneksi database.', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data kategori.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Cari kategori berdasarkan ID
            $kategori = Kategori::findOrFail($id);

            // Validasi input
            $validated = $request->validate([
                'nama_kategori' => 'required|string|max:255',
                'deskripsi_kategori' => 'nullable|string|max:255',
            ]);

            // Perbarui data kategori
            $kategori->update($validated);

            // Berikan respons sukses
            return response()->json([
                'message' => 'Kategori berhasil diperbarui.',
                'data' => $kategori,
            ], 200);
        // } catch (ModelNotFoundException $e) {
        //     // Penanganan error jika data tidak ditemukan
        //     return response()->json([
        //         'message' => 'Kategori tidak ditemukan.',
        //     ], 404);
        } catch (\PDOException $e) {
            // Penanganan error koneksi database
            return response()->json([
                'message' => 'Terjadi kesalahan pada koneksi database.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            // Penanganan error umum
            return response()->json([
                'message' => 'Gagal memperbarui kategori.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            // Cari kategori berdasarkan ID
            $kategori = Kategori::findOrFail($id);

            // Hapus kategori
            $kategori->delete();

            // Berikan respons sukses
            return response()->json([
                'message' => 'Data berhasil dihapus.',
            ], 200);

        // } catch (ModelNotFoundException $e) {
        //     // Penanganan error jika data tidak ditemukan
        //     return response()->json([
        //         'message' => 'Kategori tidak ditemukan.',
        //     ], 404);
        } catch (\PDOException $e) {
            // Penanganan error koneksi database
            return response()->json([
                'message' => 'Terjadi kesalahan pada koneksi database.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            // Penanganan error umum
            return response()->json([
                'message' => 'Gagal menghapus kategori.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
