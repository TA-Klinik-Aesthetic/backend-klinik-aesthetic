<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KompensasiDiberikan;
use App\Models\Komplain;

class KompensasiDiberikanController extends Controller
{
    // Menampilkan semua data
    public function index()
    {
        return response()->json(KompensasiDiberikan::with('komplain.user', 'kompensasi')->get());
    }

    // Menampilkan satu data berdasarkan ID
    public function show($id)
    {
        $data = KompensasiDiberikan::with('komplain.user', 'kompensasi')->findOrFail($id);
        return response()->json($data);
    }

    // Menyimpan data (bisa lebih dari satu)
    public function store(Request $request)
    {
        $data = $request->validate([
            '*.id_komplain' => 'required|exists:tb_komplain,id_komplain',
            '*.id_kompensasi' => 'required|exists:tb_kompensasi,id_kompensasi',
            '*.kode_kompensasi' => 'required|string|unique:tb_kompensasi_diberikan,kode_kompensasi',
            '*.tanggal_berakhir_kompensasi' => 'required|date',
        ]);

        $inserted = [];
        foreach ($data as $item) {
            $inserted[] = KompensasiDiberikan::create($item);
        
            // Update pemberian_kompensasi untuk setiap komplain yang diproses
            $komplain = Komplain::find($item['id_komplain']);
            if ($komplain) {
                $komplain->update(['pemberian_kompensasi' => 'Sudah dikirim']);
            }
        }
        


        return response()->json($inserted, 201);
    }

    // // Mengupdate data berdasarkan ID
    // public function update(Request $request, $id)
    // {
    //     $kompensasi = KompensasiDiberikan::findOrFail($id);

    //     $data = $request->validate([
    //         'id_kompensasi' => 'required|exists:tb_kompensasi,id_kompensasi',
    //         'kode_kompensasi' => 'required|string|unique:tb_kompensasi_diberikan,kode_kompensasi,' . $id . ',id_kompensasi_diberikan',
    //         'tanggal_berakhir_kompensasi' => 'required|date',
    //     ]);

    //     $kompensasi->update($data);
    //     return response()->json($kompensasi);
    // }
}
