<?php

namespace App\Http\Controllers;

use App\Models\Konsultasi;
use App\Models\DetailKonsultasi;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class KonsultasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $konsultasi = Konsultasi::with(['user', 'dokter', 'detail_konsultasi'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Data Konsultasi',
            'data' => $konsultasi
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => 'nullable|exists:tb_user,id_user',
            'waktu_konsultasi' => 'required|date|after:now',
            'id_dokter' => 'nullable|exists:tb_dokter,id_dokter',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $konsultasi = Konsultasi::create([
            'id_user' => $request->id_user,
            'waktu_konsultasi' => $request->waktu_konsultasi,
            'id_dokter' => $request->id_dokter,
        ]);

        $detailKonsultasi = DetailKonsultasi::create([
            'id_konsultasi' => $konsultasi->id_konsultasi,
            'keluhan_pelanggan' => '',
            'saran_tindakan' => '',
        ]);

        $detailKonsultasi->load('konsultasi');

        return response()->json([
            'success' => true,
            'message' => 'Data konsultasi dan detail konsultasi berhasil ditambahkan',
            'data' => [
                'konsultasi' => $konsultasi,
                'detail_konsultasi' => $detailKonsultasi,
            ]
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $konsultasi = Konsultasi::with(['user', 'dokter', 'detail_konsultasi'])->find($id);

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
     * Display the detail of the specified resource.
     */
    public function showDetail($id)
    {
        $detailKonsultasi = DetailKonsultasi::where('id_konsultasi', $id)->first();

        if ($detailKonsultasi) {
            return response()->json([
                'success' => true,
                'data' => $detailKonsultasi
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Detail konsultasi tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'id_dokter' => 'nullable|exists:tb_dokter,id_dokter',
            'waktu_konsultasi' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $konsultasi = Konsultasi::find($id);

        if (!$konsultasi) {
            return response()->json([
                'success' => false,
                'message' => 'Data konsultasi tidak ditemukan'
            ], 404);
        }

        if ($request->has('id_dokter')) {
            $konsultasi->id_dokter = $request->id_dokter;
        }

        if ($request->has('waktu_konsultasi')) {
            $konsultasi->waktu_konsultasi = $request->waktu_konsultasi;
        }

        $konsultasi->save();

        return response()->json([
            'success' => true,
            'message' => 'Data konsultasi berhasil diperbarui',
            'data' => $konsultasi
        ], 200);
    }

    /**
     * Update consultation by ID.
     */
    public function updateByKonsultasi(Request $request, $id_konsultasi)
    {
        $validator = Validator::make($request->all(), [
            'id_dokter' => 'required|exists:tb_dokter,id_dokter',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $konsultasi = Konsultasi::find($id_konsultasi);

        if (!$konsultasi) {
            return response()->json([
                'success' => false,
                'message' => 'Konsultasi tidak ditemukan'
            ], 404);
        }

        if ($request->has('id_dokter')) {
            $konsultasi->id_dokter = $request->id_dokter;
        }

        $konsultasi->save();

        return response()->json([
            'success' => true,
            'message' => 'Data konsultasi berhasil diperbarui',
            'data' => $konsultasi
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id_konsultasi)
    {
        $konsultasi = Konsultasi::find($id_konsultasi);

        if (!$konsultasi) {
            return response()->json([
                'success' => false,
                'message' => 'Data konsultasi tidak ditemukan',
            ], 404);
        }

        $konsultasi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data konsultasi dan detail konsultasi berhasil dihapus',
        ], 200);
    }
}
