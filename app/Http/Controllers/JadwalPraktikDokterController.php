<?php

namespace App\Http\Controllers;

use App\Models\JadwalPraktikDokter;
use App\Models\Dokter;
use Illuminate\Http\Request;

class JadwalPraktikDokterController extends Controller
{
    // Menampilkan semua jadwal praktik dokter (opsional)
    public function index()
    {
        $jadwal = JadwalPraktikDokter::with('dokter')->get();
        return response()->json($jadwal);
    }

    // Membuat jadwal praktik dokter baru
    public function store(Request $request)
    {
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
    }

    // Mengupdate jadwal praktik dokter
    public function update(Request $request, $id)
    {
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
    }

    // Menghapus jadwal praktik dokter
    public function destroy($id)
    {
        $jadwal = JadwalPraktikDokter::findOrFail($id);
        $jadwal->delete();

        return response()->json([
            'message' => 'Jadwal praktik dokter berhasil dihapus.'
        ]);
    }
}
