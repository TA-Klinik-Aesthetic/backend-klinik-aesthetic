<?php

namespace App\Http\Controllers;

use App\Models\DetailKonsultasi;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;

class DetailKonsultasiController extends Controller
{

    /**
     * Menambahkan atau memperbarui data detail konsultasi oleh dokter
     */
    public function store(Request $request, $id): JsonResponse
    {
        // Validasi input
        $validatedData = $request->validate([
            'keluhan_pelanggan' => 'required|string|max:255',
            'saran_tindakan' => 'required|string|max:255',
        ]);

        // Cari data detail konsultasi berdasarkan ID
        $detailKonsultasi = DetailKonsultasi::find($id);

        if (!$detailKonsultasi) {
            // Jika detail konsultasi tidak ditemukan, kembalikan pesan error
            return response()->json(['message' => 'Detail konsultasi tidak ditemukan.'], 404);
        }

        // Perbarui data dengan input yang divalidasi
        $detailKonsultasi->update($validatedData);

        return response()->json([
            'message' => 'Detail konsultasi berhasil diperbarui.',
            'data' => $detailKonsultasi
        ], 200);
    }
}
