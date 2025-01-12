<?php

namespace App\Http\Controllers;

use App\Models\JadwalPraktikBeautician;
use App\Models\Beautician;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class JadwalPraktikBeauticianController extends Controller
{
    // Menampilkan semua jadwal (opsional)
    public function index()
    {
        try {
            $jadwal = JadwalPraktikBeautician::with('beautician')->get();
            return response()->json($jadwal);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat mengambil data jadwal praktik.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // Membuat jadwal praktik baru
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id_beautician' => 'required|exists:tb_beautician,id_beautician',
                'hari'          => 'required|string|max:10',
                'tgl_kerja'     => 'required|date',
                'jam_mulai'     => 'required|date_format:H:i',
                'jam_selesai'   => 'required|date_format:H:i|after:jam_mulai',
            ]);

            $jadwal = JadwalPraktikBeautician::create($validatedData);

            return response()->json([
                'message' => 'Jadwal praktik berhasil dibuat.',
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

    // Mengupdate jadwal praktik
    public function update(Request $request, $id)
    {
        try {
            $jadwal = JadwalPraktikBeautician::findOrFail($id);

            $validatedData = $request->validate([
                'id_beautician' => 'required|exists:tb_beautician,id_beautician',
                'hari'          => 'required|string|max:10',
                'tgl_kerja'     => 'required|date',
                'jam_mulai'     => 'required|date_format:H:i',
                'jam_selesai'   => 'required|date_format:H:i|after:jam_mulai',
            ]);

            $jadwal->update($validatedData);

            return response()->json([
                'message' => 'Jadwal praktik berhasil diperbarui.',
                'data'    => $jadwal
            ]);
        } catch (NotFoundHttpException $e) {
            return response()->json([
                'error' => 'Jadwal praktik tidak ditemukan.'
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

    // Menghapus jadwal praktik
    public function destroy($id)
    {
        try {
            $jadwal = JadwalPraktikBeautician::findOrFail($id);
            $jadwal->delete();

            return response()->json([
                'message' => 'Jadwal praktik berhasil dihapus.'
            ]);
        } catch (NotFoundHttpException $e) {
            return response()->json([
                'error' => 'Jadwal praktik tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat menghapus jadwal praktik.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
