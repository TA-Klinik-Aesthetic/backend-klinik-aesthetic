<?php

namespace App\Http\Controllers;
use App\Models\Konsultasi;
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
        $konsultasi = Konsultasi::with(['user', 'dokter', 'detailKonsultasi', 'feedback'])->get();

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
            'id_dokter' => 'required|exists:tb_dokter,id',
            'id_detail_konsultasi' => 'nullable|exists:tb_detail_konsultasi,id',
            'id_feedback' => 'nullable|exists:tb_feedback,id',
            'waktu_konsultasi' => 'nullable|date',
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
            'id_dokter' => $request->id_dokter,
            'id_detail_konsultasi' => $request->id_detail_konsultasi,
            'id_feedback' => $request->id_feedback,
            'waktu_konsultasi' => $request->waktu_konsultasi,
        ]);

        // Kembalikan respon sukses
        return response()->json([
            'success' => true,
            'message' => 'Data konsultasi berhasil ditambahkan',
            'data' => $konsultasi
        ], 201);
    }

    // Menambahkan atau memperbarui konsultasi berdasarkan id_konsultasi
    public function updateByKonsultasi(Request $request, $id_konsultasi)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'id_user' => 'nullable|exists:tb_user,id',
            'id_detail_konsultasi' => 'nullable|exists:tb_detail_konsultasi,id',
            'id_feedback' => 'nullable|exists:tb_feedback,id',
            'waktu_konsultasi' => 'nullable|date', // Pastikan waktu konsultasi valid
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

        // Ambil id_dokter dari konsultasi yang ada
        $id_dokter = $konsultasi->id_dokter;

        // Menyusun array data yang akan diupdate
        $updatedData = [];

        // Periksa setiap field dan perbarui hanya jika ada data yang valid
        if ($request->has('id_user')) {
            $updatedData['id_user'] = $request->id_user;
        }
        if ($request->has('id_detail_konsultasi')) {
            $updatedData['id_detail_konsultasi'] = $request->id_detail_konsultasi;
        }
        if ($request->has('id_feedback')) {
            $updatedData['id_feedback'] = $request->id_feedback;
        }
        if ($request->has('waktu_konsultasi')) {
            $updatedData['waktu_konsultasi'] = $request->waktu_konsultasi;
        }

        // Perbarui data konsultasi dengan data baru (hanya yang ada)
        $konsultasi->update($updatedData);

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
        //
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
