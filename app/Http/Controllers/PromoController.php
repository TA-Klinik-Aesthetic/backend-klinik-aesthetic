<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promo;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;


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
                'jenis_promo' => 'required|string',
                'deskripsi_promo' => 'required|string',
                'tipe_potongan' => 'required|string',
                'potongan_harga' => 'required|numeric|min:0',
                'minimal_belanja' => 'nullable|numeric|min:0',
                'tanggal_mulai' => 'required|date',
                'tanggal_berakhir' => 'required|date|after_or_equal:tanggal_mulai',
                'gambar_promo' => 'nullable|image|mimes:jpeg,png,jpg,gif', // Validasi file gambar
                'status_promo' => 'required|string',
            ]);

            // Validasi tambahan: jika tipe_potongan Diskon, maksimal 99
            if ($validated['tipe_potongan'] === 'Diskon' && $validated['potongan_harga'] > 99) {
                return response()->json([
                    'message' => 'Potongan diskon tidak boleh lebih dari 99%.'
                ], 422);
            }

            // Jika ada file gambar, simpan ke storage
            if ($request->hasFile('gambar_promo')) {
                $file = $request->file('gambar_promo');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('promo_images'), $fileName);
                $validated['gambar_promo'] = 'promo_images/' . $fileName;
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

    public function update(Request $request, $id)
    {
        try {
            // 1) Cari promo
            $promo = Promo::findOrFail($id);

            // 2) Validasi input
            $validated = $request->validate([
                'nama_promo'          => 'sometimes|required|string|max:255',
                'jenis_promo'         => 'sometimes|required|string',
                'deskripsi_promo'     => 'sometimes|required|string',
                'tipe_potongan'       => 'sometimes|required|in:Diskon,Rupiah',
                'potongan_harga'      => 'sometimes|required|numeric|min:0',
                'minimal_belanja'     => 'nullable|numeric|min:0',
                'tanggal_mulai'       => 'sometimes|required|date',
                'tanggal_berakhir'    => 'sometimes|required|date|after_or_equal:tanggal_mulai',
                'status_promo'        => 'sometimes|required|string',
                'gambar_promo'        => 'nullable|image|mimes:jpeg,png,jpg,gif',
            ]);

            // 3) Validasi tambahan untuk diskon
            if (($validated['tipe_potongan'] ?? $promo->tipe_potongan) === 'Diskon'
                && ($validated['potongan_harga'] ?? $promo->potongan_harga) > 99
            ) {
                return response()->json([
                    'message' => 'Potongan diskon tidak boleh lebih dari 99%.'
                ], 422);
            }

            // 4) Jika ada upload gambar baru, hapus lama + simpan baru
            if ($request->hasFile('gambar_promo')) {
                if ($promo->gambar_promo && file_exists(public_path($promo->gambar_promo))) {
                    unlink(public_path($promo->gambar_promo));
                }
    
                $file = $request->file('gambar_promo');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('promo_images'), $fileName);
                $validated['gambar_promo'] = 'promo_images/' . $fileName;
            }

            // 5) Update
            $promo->update($validated);

            return response()->json([
                'message' => 'Promo berhasil diperbarui.',
                'data'    => $promo,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi data gagal.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Promo tidak ditemukan.',
            ], 404);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan di database.',
                'error'   => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan yang tidak terduga.',
                'error'   => $e->getMessage(),
            ], 500);
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
