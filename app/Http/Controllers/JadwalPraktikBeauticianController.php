<?php

namespace App\Http\Controllers;

use App\Models\JadwalPraktikBeautician;
use App\Models\Beautician;

use Illuminate\Http\Request;

class JadwalPraktikBeauticianController extends Controller
{
    // Menampilkan semua jadwal (opsional)
    public function index()
    {
        $jadwal = JadwalPraktikBeautician::with('beautician')->get();
        return response()->json($jadwal);
    }

    // Membuat jadwal praktik baru
    public function store(Request $request)
    {
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
    }

    // Mengupdate jadwal praktik
    public function update(Request $request, $id)
    {
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
    }

    // Menghapus jadwal praktik
    public function destroy($id)
    {
        $jadwal = JadwalPraktikBeautician::findOrFail($id);
        $jadwal->delete();

        return response()->json([
            'message' => 'Jadwal praktik berhasil dihapus.'
        ]);
    }
}
