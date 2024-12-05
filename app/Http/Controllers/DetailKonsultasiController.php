<?php

namespace App\Http\Controllers;

use App\Models\DetailKonsultasi;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;

class DetailKonsultasiController extends Controller
{
    public function connect(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'id_konsultasi' => 'required|exists:tb_konsultasi,id',
        ], [
            'id_konsultasi.required' => 'ID konsultasi harus diisi.',
            'id_konsultasi.exists' => 'ID konsultasi tidak ditemukan.',
        ]);

        $idKonsultasi = $validatedData['id_konsultasi'];

        // Buat entri baru dengan data default
        $detailKonsultasi = DetailKonsultasi::create([
            'id_konsultasi' => $idKonsultasi,
            'keluhan_pelanggan' => '',
            'saran_tindakan' => '',
        ]);

        // Include data konsultasi menggunakan relasi
        $detailKonsultasi->load('konsultasi');

        return response()->json([
            'message' => 'Detail konsultasi baru berhasil dibuat.',
            'data' => $detailKonsultasi
        ], 201);
    }

    /**
     * Menambahkan atau memperbarui data detail konsultasi oleh dokter
     */
    public function store(Request $request, $id): JsonResponse
    {
        // Validasi input
        $validatedData = $request->validate([
            'keluhan_pelanggan' => 'required|string|max:255',
            'saran_tindakan' => 'required|string|max:255',
        ], [
            'keluhan_pelanggan.required' => 'Keluhan pelanggan harus diisi.',
            'saran_tindakan.required' => 'Saran tindakan harus diisi.',
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
