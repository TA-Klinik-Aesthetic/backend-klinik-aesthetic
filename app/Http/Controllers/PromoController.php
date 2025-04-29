<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promo;
use Exception;
use Illuminate\Support\Facades\Validator;

class PromoController extends Controller
{
    // Mendapatkan semua promo
    public function index()
    {
        $promos = Promo::all();

        return response()->json([
            'success' => true,
            'message' => 'Daftar promo berhasil diambil',
            'data' => $promos,
        ]);
    }

    // Menambahkan promo baru
    public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'nama_promo' => 'required|string|max:255',
            'deskripsi_promo' => 'required|string',
            'potongan_harga' => 'required|numeric|min:0',
            'tanggal_mulai' => 'required|date',
            'tanggal_berakhir' => 'required|date|after_or_equal:tanggal_mulai',
            'gambar_promo' => 'nullable|image|mimes:jpeg,png,jpg,gif', // Validasi file gambar
            'status_promo' => 'required|string',
        ]);

        // Jika ada file gambar, simpan ke storage
        if ($request->hasFile('gambar_promo')) {
            $file = $request->file('gambar_promo');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('promo_images', $fileName, 'public');

            if (!$path) {
                return response()->json(['message' => 'Gagal menyimpan gambar'], 500);
            }

            $validated['gambar_promo'] = $path; // Simpan path ke database
        }

        $promo = Promo::create($validated);

        return response()->json([
            'message' => 'Promo berhasil ditambahkan.',
            'data' => $promo,
        ], 201);
    } catch (\PDOException $e) {
        return response()->json([
            'message' => 'Terjadi kesalahan pada koneksi database.',
            'error' => $e->getMessage(),
        ], 500);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Gagal menambahkan promo.',
            'error' => $e->getMessage(),
        ], 400);
    }
}


    // Menampilkan detail promo berdasarkan ID
    public function show($id)
    {
        try {
            $promo = Promo::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail promo berhasil diambil',
                'data' => $promo,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Promo tidak ditemukan',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
    
    // Mengupdate promo
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_promo' => 'sometimes|required|string|max:255',
            'deskripsi_promo' => 'sometimes|required|string',
            'potongan_harga' => 'sometimes|required|numeric|min:0',
            'tanggal_mulai' => 'sometimes|required|date',
            'tanggal_berakhir' => 'sometimes|required|date|after_or_equal:tanggal_mulai',
            'gambar_promo' => 'nullable|string',
            'status_promo' => 'sometimes|required|in:aktif,nonaktif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $promo = Promo::findOrFail($id);
            $promo->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Promo berhasil diubah',
                'data' => $promo,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah promo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Menghapus promo
    public function destroy($id)
    {
        try {
            $promo = Promo::findOrFail($id);
            $promo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Promo berhasil dihapus',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus promo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
