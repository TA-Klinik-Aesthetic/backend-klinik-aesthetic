<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackControllerKonsultasi extends Controller
{
    // GET: List semua feedback
    public function index()
    {
        $feedbacks = Feedback::with('konsultasi')->get();
        return response()->json($feedbacks, 200);
    }

    // POST: Tambah feedback baru
    public function store(Request $request)
    {
        $request->validate([
            'id_konsultasi' => 'required|exists:tb_konsultasi,id_konsultasi',
            'rating' => 'required|integer|min:1|max:5',
            'teks_feedback' => 'required|string',
            'balasan_feedback' => 'nullable|string',
        ]);

        $feedback = Feedback::create($request->all());
        return response()->json([
            'message' => 'Feedback berhasil ditambahkan',
            'data' => $feedback,
        ], 201);
    }

    // GET: Detail feedback berdasarkan ID
    public function show($id_feedback_konsultasi)
    {
        $feedback = Feedback::with('konsultasi')->find($id_feedback_konsultasi);

        if (!$feedback) {
            return response()->json(['message' => 'Feedback tidak ditemukan'], 404);
        }

        return response()->json($feedback, 200);
    }

    // PUT: Update feedback
    public function update(Request $request, $id_feedback_konsultasi)
    {
        $feedback = Feedback::find($id_feedback_konsultasi);

        if (!$feedback) {
            return response()->json(['message' => 'Feedback tidak ditemukan'], 404);
        }

        $request->validate([
            'id_konsultasi' => 'exists:tb_konsultasi,id_konsultasi',
            'rating' => 'integer|min:1|max:5',
            'teks_feedback' => 'string',
            'balasan_feedback' => 'nullable|string',
        ]);

        $feedback->update($request->all());

        return response()->json([
            'message' => 'Feedback berhasil diperbarui',
            'data' => $feedback,
        ], 200);
    }

    // DELETE: Hapus feedback
    public function destroy($id_feedback_konsultasi)
    {
        $feedback = Feedback::find($id_feedback_konsultasi);

        if (!$feedback) {
            return response()->json(['message' => 'Feedback tidak ditemukan'], 404);
        }

        $feedback->delete();

        return response()->json(['message' => 'Feedback berhasil dihapus'], 200);
    }
}
