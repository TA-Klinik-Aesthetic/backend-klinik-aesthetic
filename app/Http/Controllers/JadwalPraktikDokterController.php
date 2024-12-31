<?php

namespace App\Http\Controllers;

use App\Models\JadwalPraktikDokter;
use App\Models\Dokter;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class JadwalPraktikDokterController extends Controller
{
    // Menampilkan semua jadwal praktik dokter (opsional)
    public function index()
    {
        try {
            $jadwal = JadwalPraktikDokter::with('dokter')->get();
            return response()->json($jadwal);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat mengambil data jadwal praktik.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // Membuat jadwal praktik dokter baru
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id_dokter'   => 'required|exists:tb_dokter,id_dokter',
                'hari'        => 'required|string|max:10',
                'tgl_kerja'   => 'required|date',
                'jam_mulai'   => 'required|date_format:H:i',
                'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            ]);

            $jadwal = JadwalPraktikDokter::create($validatedData);

            return response()->json([
                'message' => 'Jadwal praktik dokter berhasil dibuat.',
                'data'    => $jadwal
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validasi gagal.',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat membuat jadwal praktik.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // Mengupdate jadwal praktik dokter
    public function update(Request $request, $id)
    {
        try {
            $jadwal = JadwalPraktikDokter::findOrFail($id);

            $validatedData = $request->validate([
                'id_dokter'   => 'required|exists:tb_dokter,id_dokter',
                'hari'        => 'required|string|max:10',
                'tgl_kerja'   => 'required|date',
                'jam_mulai'   => 'required|date_format:H:i',
                'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            ]);

            $jadwal->update($validatedData);

            return response()->json([
                'message' => 'Jadwal praktik dokter berhasil diperbarui.',
                'data'    => $jadwal
            ]);
        } catch (NotFoundHttpException $e) {
            return response()->json([
                'error' => 'Jadwal praktik dokter tidak ditemukan.'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validasi gagal.',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat memperbarui jadwal praktik.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // Menghapus jadwal praktik dokter
    public function destroy($id)
    {
        try {
            $jadwal = JadwalPraktikDokter::findOrFail($id);
            $jadwal->delete();

            return response()->json([
                'message' => 'Jadwal praktik dokter berhasil dihapus.'
            ]);
        } catch (NotFoundHttpException $e) {
            return response()->json([
                'error' => 'Jadwal praktik dokter tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat menghapus jadwal praktik.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
