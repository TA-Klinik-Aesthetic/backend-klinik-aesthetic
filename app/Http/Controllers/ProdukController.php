<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Favorite;
use Illuminate\Http\Request;
use App\Models\InventarisStok;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        try {
            $userId = $request->query('id_user');
            $produk = Produk::with('kategori')->get();

            if ($userId) {
                foreach ($produk as $item) {
                    $item->is_favorited = $item->isFavoritedBy($userId);
                    $item->favorites_count = $item->getFavoritesCountAttribute();
                }
            }

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

    public function show($id, Request $request)
    {
        try {
            $userId = $request->query('id_user');
            $produk = Produk::with('kategori')->findOrFail($id);

            if ($userId) {
                $produk->is_favorited = $produk->isFavoritedBy($userId);
                $produk->favorites_count = $produk->getFavoritesCountAttribute();
            }

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
                'gambar_produk' => 'nullable|image|mimes:jpeg,png,jpg,gif', // Validasi file gambar
            ]);

            // Jika ada file gambar, simpan ke storage
            if ($request->hasFile('gambar_produk')) {
                $file = $request->file('gambar_produk');
                $fileName = time() . '_' . $file->getClientOriginalName();

                // Simpan langsung ke public/produk_images
                $file->move(public_path('produk_images'), $fileName);

                // Simpan path-nya ke database
                $validated['gambar_produk'] = 'produk_images/' . $fileName;
            }

            $produk = Produk::create($validated);

            // InventarisStok::create([
            //     'id_produk' => $produk->id_produk,
            //     'status_perubahan' => 'masuk',
            //     'jumlah_perubahan' => $validated['stok_produk'],
            // ]);

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
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $produk = Produk::findOrFail($id);

            // 1) Validasi input, termasuk file gambar jika ada
            $validated = $request->validate([
                'id_kategori'      => 'nullable|exists:tb_kategori,id_kategori',
                'nama_produk'      => 'nullable|string|max:255',
                'deskripsi_produk' => 'nullable|string',
                'harga_produk'     => 'nullable|numeric',
                'stok_produk'      => 'nullable|integer',
                'status_produk'    => 'nullable|string|max:255',
                'gambar_produk'    => 'nullable|image|mimes:jpeg,png,jpg,gif',
            ]);

            // 2) Jika ada file gambar baru, hapus file lama dan simpan yang baru
            if ($request->hasFile('gambar_produk')) {
                // Hapus gambar lama jika ada
                $oldPath = public_path($produk->gambar_produk);
                if ($produk->gambar_produk && file_exists($oldPath)) {
                    unlink($oldPath);
                }

                // Upload gambar baru
                $file = $request->file('gambar_produk');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('produk_images'), $fileName);

                $validated['gambar_produk'] = 'produk_images/' . $fileName;
            }

            // 3) Update semua atribut yang tervalidasi
            $produk->update($validated);

            return response()->json([
                'message' => 'Produk berhasil diperbarui.',
                'data'    => $produk,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi data gagal',
                'errors'  => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui produk',
                'error'   => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui produk',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $produk = Produk::findOrFail($id);

            // Hapus gambar dari storage jika ada
            if (!empty($produk->gambar_produk)) {
                $gambarPath = public_path('storage/' . $produk->gambar_produk);
                if (file_exists($gambarPath)) {
                    unlink($gambarPath);
                }
            }

            // Hapus produk dari database
            $produk->delete();

            return response()->json([
                'message' => 'Produk dan gambarnya berhasil dihapus.',
            ], 200);
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

    // Method lainnya tetap sama
    public function toggleFavorite(Request $request)
    {
        $request->validate([
            'id_user' => 'required|exists:tb_user,id_user',
            'id_produk' => 'required|exists:tb_produk,id_produk'
        ]);

        $userId = $request->id_user;
        $produkId = $request->id_produk;

        $favorite = Favorite::where('id_user', $userId)
            ->where('id_produk', $produkId)
            ->first();

        if ($favorite) {
            // Jika sudah favorit, hapus dari favorit
            $favorite->delete();
            $message = 'Produk dihapus dari favorit';
            $status = false;
        } else {
            // Jika belum favorit, tambahkan ke favorit
            Favorite::create([
                'id_user' => $userId,
                'id_produk' => $produkId
            ]);
            $message = 'Produk ditambahkan ke favorit';
            $status = true;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_favorited' => $status
        ]);
    }

    public function getProdukByKategori($id_kategori, Request $request)
    {
        try {
            $userId = $request->query('id_user');
            $produk = Produk::where('id_kategori', $id_kategori)->get();

            if ($produk->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada produk untuk kategori ini.',
                    'data' => [],
                ], 404);
            }

            if ($userId) {
                foreach ($produk as $item) {
                    $item->is_favorited = $item->isFavoritedBy($userId);
                    $item->favorites_count = $item->getFavoritesCountAttribute();
                }
            }

            return response()->json([
                'message' => 'Produk ditemukan.',
                'data' => $produk,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data produk.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
