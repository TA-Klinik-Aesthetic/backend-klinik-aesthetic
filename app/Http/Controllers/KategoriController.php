<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class KategoriController extends Controller
{
    public function index()
    {
        try {
            $kategori = Kategori::all();
            return response()->json($kategori, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data kategori.', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_kategori' => 'required|string|max:255',
            ]);

            $kategori = Kategori::create($validated);

            return response()->json(['message' => 'Kategori berhasil ditambahkan.', 'data' => $kategori], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menambahkan kategori.', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $kategori = Kategori::findOrFail($id);
            return response()->json($kategori, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Kategori tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data kategori.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $kategori = Kategori::findOrFail($id);

            $validated = $request->validate([
                'nama_kategori' => 'required|string|max:255',
                'deskripsi_kategori' => 'nullable|string|max:255',
            ]);

            $kategori->update($validated);

            return response()->json(['message' => 'Kategori berhasil diperbarui.', 'data' => $kategori], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Kategori tidak ditemukan.'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui kategori.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $kategori = Kategori::findOrFail($id);
            $kategori->delete();

            return response()->json(['message' => 'Kategori berhasil dihapus.'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Kategori tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus kategori.', 'error' => $e->getMessage()], 500);
        }
    }
}
