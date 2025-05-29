<?php

namespace App\Http\Controllers;

use App\Models\KeranjangPembelian;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KeranjangPembelianController extends Controller
{
    // Tampilkan semua keranjang user berdasarkan id_user dari request
    public function index(Request $request)
    {
        $request->validate([
            'id_user' => 'required|exists:tb_user,id_user',
        ]);

        $keranjang = KeranjangPembelian::with('produk')
            ->where('id_user', $request->id_user)
            ->get();

        return response()->json($keranjang);
    }

    // Simpan produk ke keranjang berdasarkan id_user dari request
    public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'required|exists:tb_user,id_user',
            'produk' => 'required|array',
            'produk.*.id_produk' => 'required|exists:tb_produk,id_produk',
            'produk.*.jumlah' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->produk as $item) {
                $produk = Produk::findOrFail($item['id_produk']);

                if ($produk->stok_produk < $item['jumlah']) {
                    throw new \Exception("Stok produk {$produk->nama_produk} tidak mencukupi");
                }

                KeranjangPembelian::updateOrCreate(
                    ['id_user' => $request->id_user, 'id_produk' => $item['id_produk']],
                    ['jumlah' => DB::raw("jumlah + {$item['jumlah']}")]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan ke keranjang',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // Update jumlah produk di keranjang berdasarkan id_keranjang_pembelian dan id_user dari request
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_user' => 'required|exists:tb_user,id_user',
            'jumlah' => 'required|integer|min:1',
        ]);

        $keranjang = KeranjangPembelian::where('id_keranjang_pembelian', $id)
            ->where('id_user', $request->id_user)
            ->firstOrFail();

        $produk = Produk::findOrFail($keranjang->id_produk);

        if ($produk->stok_produk < $request->jumlah) {
            return response()->json([
                'success' => false,
                'message' => "Stok produk {$produk->nama_produk} tidak mencukupi",
            ], 400);
        }

        $keranjang->update(['jumlah' => $request->jumlah]);

        return response()->json([
            'success' => true,
            'message' => 'Jumlah produk di keranjang diperbarui',
        ]);
    }

    // Hapus produk di keranjang berdasarkan id_keranjang_pembelian dan id_user dari request
    public function destroy(Request $request, $id)
    {
        $request->validate([
            'id_user' => 'required|exists:tb_user,id_user',
        ]);

        $deleted = KeranjangPembelian::where('id_keranjang_pembelian', $id)
            ->where('id_user', $request->id_user)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Produk dihapus dari keranjang',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data tidak ditemukan',
        ], 404);
    }
}
