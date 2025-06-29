<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeedbackKonsultasi;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class FeedbackControllerKonsultasi extends Controller
{
    // GET: List semua feedback
    public function index()
    {
        try {
            $feedbacks = FeedbackKonsultasi::with('konsultasi')->get();

            return response()->json(['data' => $feedbacks], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Gagal mengambil data feedback',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan yang tidak terduga',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // POST: Tambah feedback baru
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id_konsultasi' => 'required|exists:tb_konsultasi,id_konsultasi',
                'rating' => 'nullable|integer|min:1|max:5',
                'teks_feedback' => 'nullable|string',
            ]);

            $feedback = FeedbackKonsultasi::create($validatedData);

            return response()->json([
                'message' => 'Feedback berhasil ditambahkan',
                'data' => $feedback,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (UnauthorizedHttpException $e) {
            return response()->json([
                'message' => 'Akses tidak diizinkan',
                'error' => $e->getMessage(),
            ], 401);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'message' => 'Akses ditolak',
                'error' => $e->getMessage(),
            ], 403);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Gagal menambahkan feedback',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan yang tidak terduga',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // GET: Detail feedback berdasarkan ID
    public function show($id_feedback_konsultasi)
    {
        try {
            $feedback = FeedbackKonsultasi::with('konsultasi')->find($id_feedback_konsultasi);

            if (!$feedback) {
                return response()->json(['message' => 'Feedback tidak ditemukan'], 404);
            }

            return response()->json(['data' => $feedback], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Gagal mengambil detail feedback',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan yang tidak terduga',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // PUT: Update feedback berdasarkan ID
    public function update(Request $request, $id_feedback_konsultasi)
    {
        try {
            $feedback = FeedbackKonsultasi::find($id_feedback_konsultasi);

            if (!$feedback) {
                return response()->json(['message' => 'Feedback tidak ditemukan'], 404);
            }

            $validatedData = $request->validate([
                'rating' => 'nullable|integer|min:1|max:5',
                'teks_feedback' => 'nullable|string',
            ]);

            $feedback->update($validatedData);

            return response()->json([
                'message' => 'Feedback berhasil diperbarui',
                'data' => $feedback,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (UnauthorizedHttpException $e) {
            return response()->json([
                'message' => 'Akses tidak diizinkan',
                'error' => $e->getMessage(),
            ], 401);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'message' => 'Akses ditolak',
                'error' => $e->getMessage(),
            ], 403);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Gagal memperbarui feedback',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan yang tidak terduga',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // DELETE: Hapus feedback berdasarkan ID
    public function destroy($id_feedback_konsultasi)
    {
        try {
            $feedback = FeedbackKonsultasi::find($id_feedback_konsultasi);

            if (!$feedback) {
                return response()->json(['message' => 'Feedback tidak ditemukan'], 404);
            }

            $feedback->delete();

            return response()->json(['message' => 'Feedback berhasil dihapus'], 200);
        } catch (UnauthorizedHttpException $e) {
            return response()->json([
                'message' => 'Akses tidak diizinkan',
                'error' => $e->getMessage(),
            ], 401);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'message' => 'Akses ditolak',
                'error' => $e->getMessage(),
            ], 403);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Gagal menghapus feedback',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan yang tidak terduga',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
