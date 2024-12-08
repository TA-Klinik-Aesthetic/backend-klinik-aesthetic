<?php

namespace App\Http\Controllers;

use App\Models\Konsultasi;
use App\Models\DetailKonsultasi;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class KonsultasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Mendapatkan seluruh data konsultasi beserta relasinya
        $konsultasi = Konsultasi::with(['user', 'dokter'])->get();

        // Mengembalikan data dalam bentuk JSON
        return response()->json([
            'success' => true,
            'message' => 'Data Konsultasi',
            'data' => $konsultasi
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi data request
        $validator = Validator::make($request->all(), [
            'id_user' => 'nullable|exists:tb_user,id',
            'waktu_konsultasi' => 'required|date|after:now',
            'id_dokter' => 'nullable|exists:tb_dokter,id',
        ]);
    
        // Jika validasi gagal, kembalikan respon error
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }
    
        // Buat data konsultasi
        $konsultasi = Konsultasi::create([
            'id_user' => $request->id_user,
            'waktu_konsultasi' => $request->waktu_konsultasi,
            'id_dokter' => $request->id_dokter,
        ]);
    
        // Buat entri baru di tabel detail_konsultasi dengan id_konsultasi yang sama
        $detailKonsultasi = DetailKonsultasi::create([
            'id_konsultasi' => $konsultasi->id,
            'keluhan_pelanggan' => '',
            'saran_tindakan' => '',
        ]);
    
        // Include data konsultasi di dalam detail konsultasi
        $detailKonsultasi->load('konsultasi');
    
        // Kembalikan respon sukses dengan data konsultasi dan detail konsultasi
        return response()->json([
            'success' => true,
            'message' => 'Data konsultasi dan detail konsultasi berhasil ditambahkan',
            'data' => [
                'konsultasi' => $konsultasi,
                'detail_konsultasi' => $detailKonsultasi,
            ]
        ], 201);
    }
    

    // Menambahkan atau memperbarui konsultasi berdasarkan id_konsultasi
    public function updateByKonsultasi(Request $request, $id_konsultasi)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'id_dokter' => 'required|exists:tb_dokter,id',
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        // Ambil data konsultasi berdasarkan id_konsultasi
        $konsultasi = Konsultasi::find($id_konsultasi);

        // Jika konsultasi tidak ditemukan
        if (!$konsultasi) {
            return response()->json([
                'success' => false,
                'message' => 'Konsultasi tidak ditemukan'
            ], 404);
        }

        // Perbarui id_dokter jika ada dalam permintaan
        if ($request->has('id_dokter')) {
            $konsultasi->id_dokter = $request->id_dokter;
        }

        // Simpan perubahan
        $konsultasi->save();

        // Kembalikan response sukses
        return response()->json([
            'success' => true,
            'message' => 'Data konsultasi berhasil diperbarui',
            'data' => $konsultasi
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        // Cari data konsultasi dengan relasi user dan dokter
        $konsultasi = Konsultasi::with(['user', 'dokter'])->find($id);

        if (!$konsultasi) {
            return response()->json([
                'success' => false,
                'message' => 'Data konsultasi tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $konsultasi,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
