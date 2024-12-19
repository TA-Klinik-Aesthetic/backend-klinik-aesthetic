<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PromoController extends Controller
{
    /**
     * Menampilkan daftar promo.
     */
    public function index()
    {
        try {
            $promos = Promo::all();
            return response()->json($promos, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengambil daftar promo', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Menambahkan promo baru.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'judul_promo' => 'required|string|max:255',
                'deskripsi_promo' => 'required|string',
                'keterangan_promo' => 'nullable|string',
                'tanggal_mulai' => 'required|date',
                'tanggal_berakhir' => 'required|date|after_or_equal:tanggal_mulai',
                'gambar_promo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'status_promo' => 'required|boolean',
            ]);

            // Mengunggah gambar promo
            if ($request->hasFile('gambar_promo')) {
                $validated['gambar_promo'] = $request->file('gambar_promo')->store('promos', 'public');
            }

            $promo = Promo::create($validated);
            return response()->json(['message' => 'Promo berhasil ditambahkan', 'data' => $promo], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validasi gagal', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menambahkan promo', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Menampilkan detail promo tertentu.
     */
    public function show($id)
    {
        try {
            $promo = Promo::findOrFail($id);
            return response()->json($promo, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Promo tidak ditemukan'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menampilkan promo', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Memperbarui promo yang sudah ada.
     */
    public function update(Request $request, $id)
    {
        try {
            $promo = Promo::findOrFail($id);

            $validated = $request->validate([
                'judul_promo' => 'sometimes|required|string|max:255',
                'deskripsi_promo' => 'sometimes|required|string',
                'keterangan_promo' => 'nullable|string',
                'tanggal_mulai' => 'sometimes|required|date',
                'tanggal_berakhir' => 'sometimes|required|date|after_or_equal:tanggal_mulai',
                'gambar_promo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'status_promo' => 'sometimes|required|boolean',
            ]);

            // Mengunggah gambar baru jika ada
            if ($request->hasFile('gambar_promo')) {
                // Hapus gambar lama
                if ($promo->gambar_promo && Storage::exists('public/' . $promo->gambar_promo)) {
                    Storage::delete('public/' . $promo->gambar_promo);
                }
                $validated['gambar_promo'] = $request->file('gambar_promo')->store('promos', 'public');
            }

            $promo->update($validated);

            return response()->json(['message' => 'Promo berhasil diperbarui', 'data' => $promo], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validasi gagal', 'messages' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Promo tidak ditemukan'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memperbarui promo', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Menghapus promo.
     */
    public function destroy($id)
    {
        try {
            $promo = Promo::findOrFail($id);

            // Hapus gambar jika ada
            if ($promo->gambar_promo && Storage::exists('public/' . $promo->gambar_promo)) {
                Storage::delete('public/' . $promo->gambar_promo);
            }

            $promo->delete();

            return response()->json(['message' => 'Promo berhasil dihapus'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Promo tidak ditemukan'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menghapus promo', 'message' => $e->getMessage()], 500);
        }
    }
}
