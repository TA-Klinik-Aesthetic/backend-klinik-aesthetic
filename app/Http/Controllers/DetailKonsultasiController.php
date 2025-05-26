<?php

namespace App\Http\Controllers;

use App\Models\DetailKonsultasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Konsultasi;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DetailKonsultasiController extends Controller
{
    /**
     * Menambahkan atau memperbarui data detail konsultasi oleh dokter
     */
    public function store(Request $request, $id): JsonResponse
    {
        try {
            // Validasi input sebagai array dari data detail konsultasi
            $validatedData = $request->validate([
                'details' => 'required|array',
                'details.*.saran_tindakan' => 'required|string|max:255',
                'details.*.id_treatment' => 'nullable|exists:tb_treatment,id_treatment',
            ]);

            // Cari data konsultasi berdasarkan ID
            $konsultasi = Konsultasi::find($id);
            if (!$konsultasi) {
                return response()->json(['message' => 'Konsultasi tidak ditemukan.'], 404);
            }

            $detailKonsultasiList = [];

            // Simpan setiap detail konsultasi ke dalam database
            foreach ($validatedData['details'] as $detail) {
                $detailKonsultasiList[] = DetailKonsultasi::create([
                    'id_konsultasi' => $id,
                    'saran_tindakan' => $detail['saran_tindakan'],
                    'id_treatment' => $detail['id_treatment'] ?? null,
                ]);
            }

            // Ubah status konsultasi menjadi 'Selesai'
            $konsultasi->status_booking_konsultasi = 'Selesai';
            $konsultasi->save();

            return response()->json([
                'message' => 'Detail konsultasi berhasil ditambahkan.',
                'data' => $detailKonsultasiList
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan yang tidak terduga.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
