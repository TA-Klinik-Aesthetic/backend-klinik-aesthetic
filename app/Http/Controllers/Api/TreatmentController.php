<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TreatmentController extends Controller
{
    // GET: List semua treatment
    public function index()
    {
        try {
            $treatments = Treatment::with('jenis_treatment')->get();
            return response()->json(['data' => $treatments], 200);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data treatment', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan yang tidak terduga', 'error' => $e->getMessage()], 500);
        }
    }

    // POST: Tambah treatment baru
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_jenis_treatment' => 'required|exists:tb_jenis_treatment,id_jenis_treatment',
                'nama_treatment' => 'required|string|max:255',
                'deskripsi_treatment' => 'nullable|string',
                'biaya_treatment' => 'required|numeric',
                'estimasi_treatment' => 'nullable|string',
                'gambar_treatment' => 'nullable|string|url',
            ]);

            $treatment = Treatment::create($validated);

            return response()->json([
                'message' => 'Treatment berhasil ditambahkan',
                'data' => $treatment,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi data gagal', 'errors' => $e->errors()], 422);
        } catch (UnauthorizedHttpException $e) {
            return response()->json(['message' => 'Akses tidak diizinkan', 'error' => $e->getMessage()], 401);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['message' => 'Akses ditolak', 'error' => $e->getMessage()], 403);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan treatment', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan yang tidak terduga', 'error' => $e->getMessage()], 500);
        }
    }

    // GET: Detail treatment berdasarkan ID
    public function show($id)
    {
        try {
            $treatment = Treatment::with('jenis_treatment')->find($id);

            if (!$treatment) {
                return response()->json(['message' => 'Treatment tidak ditemukan'], 404);
            }

            return response()->json(['data' => $treatment], 200);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data treatment', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan yang tidak terduga', 'error' => $e->getMessage()], 500);
        }
    }

    // PUT: Update treatment berdasarkan ID
    public function update(Request $request, $id)
    {
        try {
            $treatment = Treatment::find($id);

            if (!$treatment) {
                return response()->json(['message' => 'Treatment tidak ditemukan'], 404);
            }

            $validated = $request->validate([
                'id_jenis_treatment' => 'exists:tb_jenis_treatment,id_jenis_treatment',
                'nama_treatment' => 'string|max:255',
                'deskripsi_treatment' => 'nullable|string',
                'biaya_treatment' => 'numeric',
                'estimasi_treatment' => 'nullable|string',
                'gambar_treatment' => 'nullable|string|url',
            ]);

            $treatment->update($validated);

            return response()->json([
                'message' => 'Treatment berhasil diperbarui',
                'data' => $treatment,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi data gagal', 'errors' => $e->errors()], 422);
        } catch (UnauthorizedHttpException $e) {
            return response()->json(['message' => 'Akses tidak diizinkan', 'error' => $e->getMessage()], 401);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['message' => 'Akses ditolak', 'error' => $e->getMessage()], 403);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat memperbarui treatment', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan yang tidak terduga', 'error' => $e->getMessage()], 500);
        }
    }

    // DELETE: Hapus treatment berdasarkan ID
    public function destroy($id)
    {
        try {
            $treatment = Treatment::find($id);

            if (!$treatment) {
                return response()->json(['message' => 'Treatment tidak ditemukan'], 404);
            }

            $treatment->delete();

            return response()->json(['message' => 'Treatment berhasil dihapus'], 200);
        } catch (UnauthorizedHttpException $e) {
            return response()->json(['message' => 'Akses tidak diizinkan', 'error' => $e->getMessage()], 401);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['message' => 'Akses ditolak', 'error' => $e->getMessage()], 403);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat menghapus treatment', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan yang tidak terduga', 'error' => $e->getMessage()], 500);
        }
    }
}
