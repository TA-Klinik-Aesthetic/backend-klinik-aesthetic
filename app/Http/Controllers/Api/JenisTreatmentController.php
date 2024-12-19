<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JenisTreatment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class JenisTreatmentController extends Controller
{
    // GET: List semua jenis treatment
    public function index()
    {
        try {
            $jenis_treatments = JenisTreatment::with('treatment')->get();
            return response()->json(['data' => $jenis_treatments], 200);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Gagal mengambil data jenis treatment', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan yang tidak terduga', 'error' => $e->getMessage()], 500);
        }
    }

    // POST: Tambah jenis treatment baru
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_jenis_treatment' => 'required|string|max:255',
            ]);

            $jenis_treatment = JenisTreatment::create($validated);

            return response()->json([
                'message' => 'Jenis Treatment berhasil ditambahkan',
                'data' => $jenis_treatment,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        } catch (UnauthorizedHttpException $e) {
            return response()->json(['message' => 'Akses tidak diizinkan', 'error' => $e->getMessage()], 401);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['message' => 'Akses ditolak', 'error' => $e->getMessage()], 403);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Gagal menambahkan jenis treatment', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan yang tidak terduga', 'error' => $e->getMessage()], 500);
        }
    }

    // GET: Detail jenis treatment berdasarkan ID
    public function show($id)
    {
        try {
            $jenis_treatment = JenisTreatment::with('treatment')->find($id);

            if (!$jenis_treatment) {
                return response()->json(['message' => 'Jenis Treatment tidak ditemukan'], 404);
            }

            return response()->json(['data' => $jenis_treatment], 200);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Gagal mengambil detail jenis treatment', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan yang tidak terduga', 'error' => $e->getMessage()], 500);
        }
    }

    // PUT: Update jenis treatment berdasarkan ID
    public function update(Request $request, $id)
    {
        try {
            $jenis_treatment = JenisTreatment::find($id);

            if (!$jenis_treatment) {
                return response()->json(['message' => 'Jenis Treatment tidak ditemukan'], 404);
            }

            $validated = $request->validate([
                'nama_jenis_treatment' => 'string|max:255',
            ]);

            $jenis_treatment->update($validated);

            return response()->json([
                'message' => 'Jenis Treatment berhasil diperbarui',
                'data' => $jenis_treatment,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        } catch (UnauthorizedHttpException $e) {
            return response()->json(['message' => 'Akses tidak diizinkan', 'error' => $e->getMessage()], 401);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['message' => 'Akses ditolak', 'error' => $e->getMessage()], 403);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Gagal memperbarui jenis treatment', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan yang tidak terduga', 'error' => $e->getMessage()], 500);
        }
    }

    // DELETE: Hapus jenis treatment berdasarkan ID
    public function destroy($id)
    {
        try {
            $jenis_treatment = JenisTreatment::find($id);

            if (!$jenis_treatment) {
                return response()->json(['message' => 'Jenis Treatment tidak ditemukan'], 404);
            }

            $jenis_treatment->delete();

            return response()->json(['message' => 'Jenis Treatment berhasil dihapus'], 200);
        } catch (UnauthorizedHttpException $e) {
            return response()->json(['message' => 'Akses tidak diizinkan', 'error' => $e->getMessage()], 401);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['message' => 'Akses ditolak', 'error' => $e->getMessage()], 403);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Gagal menghapus jenis treatment', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan yang tidak terduga', 'error' => $e->getMessage()], 500);
        }
    }
}
