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
            $validator = Validator::make($request->all(), [
                'nama_promo' => 'required|string|max:255',
                'deskripsi_promo' => 'required|string',
                'potongan_harga' => 'required|numeric|min:0',
                'tanggal_mulai' => 'required|date',
                'tanggal_berakhir' => 'required|date|after_or_equal:tanggal_mulai',
                'gambar_promo' => 'nullable|string',
                'status_promo' => 'required|in:aktif,nonaktif',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }
    
            try {
                $promo = Promo::create($request->all());
    
                return response()->json([
                    'success' => true,
                    'message' => 'Promo berhasil ditambahkan',
                    'data' => $promo,
                ], 201);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan promo',
                    'error' => $e->getMessage(),
                ], 500);
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
