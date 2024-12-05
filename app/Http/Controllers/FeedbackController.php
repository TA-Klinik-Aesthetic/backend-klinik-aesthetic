<?php

namespace App\Http\Controllers;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function connect(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'id_konsultasi' => 'required|exists:tb_konsultasi,id',
        ], [
            'id_konsultasi.required' => 'ID konsultasi harus diisi.',
            'id_konsultasi.exists' => 'ID konsultasi tidak ditemukan.',
        ]);

        $idKonsultasi = $validatedData['id_konsultasi'];

        // Cek apakah sudah ada entri di tb_feedback
        $existingFeedback = Feedback::with('konsultasi')->where('id_konsultasi', $idKonsultasi)->first();

        if ($existingFeedback) {
            return response()->json([
                'message' => 'Feedback sudah terhubung.',
                'data' => $existingFeedback
            ], 200);
        }

        // Jika belum ada, buat entri baru dengan data default
        $feedback = Feedback::create([
            'id_konsultasi' => $idKonsultasi,
            'rating' => 0, // Default value
            'teks_feedback' => '',
            'balasan_feedback' => '',
        ]);

        // Include data konsultasi menggunakan relasi
        $feedback->load('konsultasi');

        return response()->json([
            'message' => 'Feedback berhasil dihubungkan.',
            'data' => $feedback
        ], 201);
    }

    public function storeTeksFeedback(Request $request, $id_konsultasi): JsonResponse
    {
        // Validasi input
        $validatedData = $request->validate([
            'teks_feedback' => 'required|string',
            'rating' => 'required|integer|min:1|max:5', // Validasi rating antara 1 hingga 5
        ], [
            'teks_feedback.required' => 'Teks feedback harus diisi.',
            'rating.required' => 'Rating harus diisi.',
            'rating.integer' => 'Rating harus berupa angka.',
            'rating.min' => 'Rating minimal bernilai 1.',
            'rating.max' => 'Rating maksimal bernilai 5.',
        ]);
    
        // Cek apakah feedback sudah ada
        $feedback = Feedback::where('id_konsultasi', $id_konsultasi)->first();
    
        if (!$feedback) {
            return response()->json([
                'message' => 'Feedback tidak ditemukan untuk konsultasi ini.'
            ], 404);
        }
    
        // Perbarui teks feedback dan rating
        $feedback->update([
            'teks_feedback' => $validatedData['teks_feedback'],
            'rating' => $validatedData['rating']
        ]);
    
        return response()->json([
            'message' => 'Teks feedback dan rating berhasil disimpan.',
            'data' => $feedback
        ], 200);
    }
    

    /**
     * Menyimpan atau memperbarui balasan feedback
     */
    public function storeBalasanFeedback(Request $request, $id_konsultasi): JsonResponse
    {
        // Validasi input
        $validatedData = $request->validate([
            'balasan_feedback' => 'required|string',
        ], [
            'balasan_feedback.required' => 'Balasan feedback harus diisi.',
        ]);

        // Cek apakah feedback sudah ada
        $feedback = Feedback::where('id_konsultasi', $id_konsultasi)->first();

        if (!$feedback) {
            return response()->json([
                'message' => 'Feedback tidak ditemukan untuk konsultasi ini.'
            ], 404);
        }

        // Perbarui balasan feedback
        $feedback->update([
            'balasan_feedback' => $validatedData['balasan_feedback']
        ]);

        return response()->json([
            'message' => 'Balasan feedback berhasil disimpan.',
            'data' => $feedback
        ], 200);
    }
}
