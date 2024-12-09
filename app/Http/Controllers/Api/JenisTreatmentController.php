<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JenisTreatment;
use Illuminate\Http\Request;

class JenisTreatmentController extends Controller
{
    // GET: List semua jenis treatment
    public function index()
    {
        $jenis_treatments = JenisTreatment::with('treatment')->get();
        return response()->json(['data' => $jenis_treatments], 200);
    }

    // POST: Tambah jenis treatment baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_jenis_treatment' => 'required|string|max:255',
        ]);

        $jenis_treatment = JenisTreatment::create($request->only('nama_jenis_treatment'));

        return response()->json(['message' => 'Jenis Treatment berhasil ditambahkan', 'data' => $jenis_treatment], 201);
    }

    // GET: Detail jenis treatment berdasarkan ID
    public function show($id)
    {
        $jenis_treatment = JenisTreatment::with('treatment')->find($id);

        if (!$jenis_treatment) {
            return response()->json(['message' => 'Jenis Treatment tidak ditemukan'], 404);
        }

        return response()->json(['data' => $jenis_treatment], 200);
    }

    // PUT: Update jenis treatment berdasarkan ID
    public function update(Request $request, $id)
    {
        $jenis_treatment = JenisTreatment::find($id);

        if (!$jenis_treatment) {
            return response()->json(['message' => 'Jenis Treatment tidak ditemukan'], 404);
        }

        $request->validate([
            'nama_jenis_treatment' => 'string|max:255',
        ]);

        $jenis_treatment->update($request->only('nama_jenis_treatment'));

        return response()->json(['message' => 'Jenis Treatment berhasil diperbarui', 'data' => $jenis_treatment], 200);
    }

    // DELETE: Hapus jenis treatment berdasarkan ID
    public function destroy($id)
    {
        $jenis_treatment = JenisTreatment::find($id);

        if (!$jenis_treatment) {
            return response()->json(['message' => 'Jenis Treatment tidak ditemukan'], 404);
        }

        $jenis_treatment->delete();
        return response()->json(['message' => 'Jenis Treatment berhasil dihapus'], 200);
    }
}
