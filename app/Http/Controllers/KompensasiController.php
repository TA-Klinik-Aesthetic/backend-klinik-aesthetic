<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kompensasi;

class KompensasiController extends Controller
{
    public function index()
    {
        return response()->json(Kompensasi::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_kompensasi' => 'required|string',
            'deskripsi_kompensasi' => 'nullable|string',
        ]);

        $kompensasi = Kompensasi::create($data);
        return response()->json($kompensasi, 201);
    }

    public function update(Request $request, $id)
    {
        $kompensasi = Kompensasi::findOrFail($id);
        $data = $request->validate([
            'nama_kompensasi' => 'required|string',
            'deskripsi_kompensasi' => 'nullable|string',
        ]);

        $kompensasi->update($data);
        return response()->json($kompensasi);
    }
}
