<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Treatment;
use Illuminate\Http\Request;

class TreatmentController extends Controller
{
    // GET: List semua treatment
    public function index()
    {
        $treatments = Treatment::with('jenis_treatment')->get();
        return response()->json(['data' => $treatments], 200);
    }

    // POST: Tambah treatment baru
    public function store(Request $request)
    {
        $request->validate([
            'id_jenis_treatment' => 'required|exists:tb_jenis_treatment,id_jenis_treatment',
            'nama_treatment' => 'required|string|max:255',
            'deskripsi_treatment' => 'nullable|string',
            'biaya_treatment' => 'required|numeric',
            'estimasi_treatment' => 'nullable|string',
            'gambar_treatment' => 'nullable|string|url',
        ]);

        $treatment = Treatment::create($request->only([
            'id_jenis_treatment',
            'nama_treatment',
            'deskripsi_treatment',
            'biaya_treatment',
            'estimasi_treatment',
            'gambar_treatment',
        ]));

        return response()->json(['message' => 'Treatment berhasil ditambahkan', 'data' => $treatment], 201);
    }

    // GET: Detail treatment berdasarkan ID
    public function show($id)
    {
        $treatment = Treatment::with('jenis_treatment')->find($id);

        if (!$treatment) {
            return response()->json(['message' => 'Treatment tidak ditemukan'], 404);
        }

        return response()->json(['data' => $treatment], 200);
    }

    // PUT: Update treatment berdasarkan ID
    public function update(Request $request, $id)
    {
        $treatment = Treatment::find($id);

        if (!$treatment) {
            return response()->json(['message' => 'Treatment tidak ditemukan'], 404);
        }

        $request->validate([
            'id_jenis_treatment' => 'exists:tb_jenis_treatment,id_jenis_treatment',
            'nama_treatment' => 'string|max:255',
            'deskripsi_treatment' => 'nullable|string',
            'biaya_treatment' => 'numeric',
            'estimasi_treatment' => 'nullable|string',
            'gambar_treatment' => 'nullable|string|url',
        ]);

        $treatment->update($request->only([
            'id_jenis_treatment',
            'nama_treatment',
            'deskripsi_treatment',
            'biaya_treatment',
            'estimasi_treatment',
            'gambar_treatment',
        ]));

        return response()->json(['message' => 'Treatment berhasil diperbarui', 'data' => $treatment], 200);
    }

    // DELETE: Hapus treatment berdasarkan ID
    public function destroy($id)
    {
        $treatment = Treatment::find($id);

        if (!$treatment) {
            return response()->json(['message' => 'Treatment tidak ditemukan'], 404);
        }

        $treatment->delete();
        return response()->json(['message' => 'Treatment berhasil dihapus'], 200);
    }
}
