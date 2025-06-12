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
                'details.*.diagnosis' => 'required|string|max:255',
                'details.*.saran_tindakan' => 'required|string|max:255',
                'details.*.id_treatment' => 'nullable|exists:tb_treatment,id_treatment',
            ]);

            // Cari data konsultasi berdasarkan ID
            $konsultasi = Konsultasi::find($id);
            if (!$konsultasi) {
                return response()->json(['message' => 'Konsultasi tidak ditemukan.'], 404);
            }

            // ❌ Hentikan jika status bukan "Berhasil Dibooking"
            if ($konsultasi->status_booking_konsultasi !== 'Berhasil dibooking') {
                return response()->json([
                    'message' => 'Detail konsultasi hanya dapat dimasukkan jika status konsultasi adalah "Berhasil Dibooking".'
                ], 403);
            }

            $detailKonsultasiList = [];

            // Simpan setiap detail konsultasi ke dalam database
            foreach ($validatedData['details'] as $detail) {
                $detailKonsultasiList[] = DetailKonsultasi::create([
                    'id_konsultasi' => $id,
                    'diagnosis'      => $detail['diagnosis'],
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

    // public function update(Request $request, $id): JsonResponse
    // {
    //     try {
    //         // 1. Validasi input
    //         $validatedData = $request->validate([
    //             // Pastikan pemeriksaan_fisik ada dan berupa string
    //             'pemeriksaan_fisik'             => 'required|string',
    //             // details adalah array, tiap elemen wajib memiliki diagnosis dan saran_tindakan
    //             'details'                       => 'required|array|min:1',
    //             'details.*.diagnosis'           => 'required|string',
    //             'details.*.saran_tindakan'      => 'required|string|max:255',
    //             'details.*.id_treatment'        => 'nullable|exists:tb_treatment,id_treatment',
    //         ]);

    //         // 2. Cari header konsultasi
    //         $konsultasi = Konsultasi::find($id);
    //         if (!$konsultasi) {
    //             return response()->json(['message' => 'Konsultasi tidak ditemukan.'], 404);
    //         }

    //         // 3. Update kolom pemeriksaan_fisik
    //         $konsultasi->pemeriksaan_fisik = $validatedData['pemeriksaan_fisik'];
    //         $konsultasi->status_booking_konsultasi = 'Selesai';
    //         $konsultasi->save();

    //         $detailKonsultasiList = [];

    //         // 4. Simpan setiap detail (diagnosis + saran_tindakan + id_treatment)
    //         foreach ($validatedData['details'] as $detail) {
    //             $detailKonsultasiList[] = DetailKonsultasi::create([
    //                 'id_konsultasi'  => $id,
    //                 'diagnosis'      => $detail['diagnosis'],
    //                 'saran_tindakan' => $detail['saran_tindakan'],
    //                 'id_treatment'   => $detail['id_treatment'] ?? null,
    //             ]);
    //         }

    //         // 5. Kembalikan respons sukses
    //         return response()->json([
    //             'message' => 'Pemeriksaan fisik dan detail konsultasi berhasil ditambahkan.',
    //             'konsultasi' => [
    //                 'id_konsultasi'          => $konsultasi->id_konsultasi,
    //                 'status_booking_konsultasi' => $konsultasi->status_booking_konsultasi,
    //                 'waktu_konsultasi'       => $konsultasi->waktu_konsultasi,
    //                 'keluhan_pelanggan'      => $konsultasi->keluhan_pelanggan,
    //                 'pemeriksaan_fisik'      => $konsultasi->pemeriksaan_fisik,
    //             ],
    //             'details'    => $detailKonsultasiList
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Terjadi kesalahan yang tidak terduga.',
    //             'error'   => $e->getMessage()
    //         ], 500);
    //     }
    // }
}
