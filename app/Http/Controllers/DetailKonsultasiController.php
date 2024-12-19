<?php

namespace App\Http\Controllers;

use App\Models\DetailKonsultasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

            // Kembalikan respons sukses
            return response()->json([
                'message' => 'Detail konsultasi berhasil diperbarui.',
                'data' => $detailKonsultasi
            ], 200);

        } catch (UnauthorizedHttpException $e) {
            // Handle error autentikasi
            return response()->json([
                'message' => 'Akses tidak diizinkan.',
                'error' => $e->getMessage()
            ], 401);
        } catch (AccessDeniedHttpException $e) {
            // Handle error otorisasi
            return response()->json([
                'message' => 'Akses ditolak.',
                'error' => $e->getMessage()
            ], 403);
        } catch (QueryException $e) {
            // Handle kesalahan query database
            return response()->json([
                'message' => 'Terjadi kesalahan pada basis data.',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            // Handle kesalahan umum lainnya
            return response()->json([
                'message' => 'Terjadi kesalahan yang tidak terduga.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
