<?php

namespace App\Http\Controllers;

use App\Models\KeranjangPembelian;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KeranjangPembelianController extends Controller
{
    // GET semua data
    public function index()
    {
        try {
            $keranjang = KeranjangPembelian::with(['user', 'produk'])->get();
            return response()->json($keranjang);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data keranjang',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // GET berdasarkan id_user
    public function getByUser($id_user)
    {
        try {
            $keranjang = KeranjangPembelian::with(['produk'])
                ->where('id_user', $id_user)
                ->get();

            if ($keranjang->isEmpty()) {
                return response()->json(['message' => 'Data tidak ditemukan untuk user ini'], 404);
            }

            return response()->json($keranjang);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data keranjang berdasarkan user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // POST: tambah data baru
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_user' => 'required|exists:tb_user,id_user',
                'id_produk' => 'required|exists:tb_produk,id_produk',
                'jumlah' => 'required|integer|min:1',
            ]);

            // Cek apakah produk ini sudah ada di keranjang user
            $existing = KeranjangPembelian::where('id_user', $validated['id_user'])
                ->where('id_produk', $validated['id_produk'])
                ->first();

            if ($existing) {
                // Jika sudah ada, tambahkan jumlah
                $existing->jumlah += $validated['jumlah'];
                $existing->save();

                return response()->json([
                    'message' => 'Jumlah produk berhasil ditambahkan ke keranjang',
                    'data' => $existing
                ], 200);
            } else {
                // Jika belum ada, buat baru
                $keranjang = KeranjangPembelian::create($validated);

                return response()->json([
                    'message' => 'Produk berhasil ditambahkan ke keranjang',
                    'data' => $keranjang
                ], 201);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menambahkan data keranjang',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // PUT: update data
    public function update(Request $request, $id)
    {
        try {
            $keranjang = KeranjangPembelian::find($id);

            if (!$keranjang) {
                return response()->json(['message' => 'Data tidak ditemukan'], 404);
            }

            $validated = $request->validate([
                'id_user' => 'sometimes|required|exists:tb_user,id_user',
                'id_produk' => 'sometimes|required|exists:tb_produk,id_produk',
                'jumlah' => 'sometimes|required|integer|min:1',
            ]);

            $keranjang->update($validated);

            return response()->json([
                'message' => 'Data berhasil diupdate',
                'data' => $keranjang
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengupdate data keranjang',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // DELETE: hapus data
    public function destroy($id)
    {
        try {
            $keranjang = KeranjangPembelian::find($id);

            if (!$keranjang) {
                return response()->json(['message' => 'Data tidak ditemukan'], 404);
            }

            $keranjang->delete();

            return response()->json(['message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus data keranjang',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // GET: Total jumlah produk dalam keranjang berdasarkan id_user
    public function getTotalProdukByUser($id_user)
    {
        try {
            $total = KeranjangPembelian::where('id_user', $id_user)->sum('jumlah');

            return response()->json([
                'id_user' => $id_user,
                'total_produk' => $total
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghitung total produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
