<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

    public function store(Request $request)
    {

        try {
            $validated = $request->validate([
                'id_jenis_treatment' => 'required|exists:tb_jenis_treatment,id_jenis_treatment',
                'nama_treatment' => 'required|string|max:255',
                'deskripsi_treatment' => 'nullable|string',
                'biaya_treatment' => 'required|numeric',
                'estimasi_treatment' => 'nullable|string',
                'gambar_treatment' => 'nullable|image|mimes:jpeg,png,jpg,gif', // Maksimal 2MB
            ]);
    
            // Jika ada file gambar, simpan ke storage
            if ($request->hasFile('gambar_treatment')) {
                $file = $request->file('gambar_treatment');
                
                // Buat nama unik agar tidak tertimpa
                $fileName = time() . '_' . $file->getClientOriginalName();
                
                // Simpan ke storage di folder 'public/treatment_images'
                $path = $file->storeAs('treatment_images', $fileName, 'public');
    
                if (!$path) {
                    return response()->json(['message' => 'Gagal menyimpan gambar'], 500);
                }
                
                $validated['gambar_treatment'] = $path; // Simpan path ke database
            }
    
            // Simpan data treatment ke database
            $treatment = Treatment::create($validated);
    
            return response()->json([
                'message' => 'Treatment berhasil ditambahkan',
                'data' => $treatment,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi data gagal', 'errors' => $e->errors()], 422);
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
    
            // Validasi input, termasuk file gambar jika ada
            $validated = $request->validate([
                'id_jenis_treatment' => 'exists:tb_jenis_treatment,id_jenis_treatment',
                'nama_treatment'     => 'string|max:255',
                'deskripsi_treatment'=> 'nullable|string',
                'biaya_treatment'    => 'numeric',
                'estimasi_treatment' => 'nullable|string',
                'gambar_treatment'   => 'nullable|image|mimes:jpeg,png,jpg,gif',
            ]);
    
            // Jika ada file gambar baru, simpan dan hapus gambar lama
            if ($request->hasFile('gambar_treatment')) {
                // Hapus file lama jika ada
                if ($treatment->gambar_treatment && Storage::disk('public')->exists($treatment->gambar_treatment)) {
                    Storage::disk('public')->delete($treatment->gambar_treatment);
                }
    
                $file     = $request->file('gambar_treatment');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path     = $file->storeAs('treatment_images', $fileName, 'public');
                if (!$path) {
                    return response()->json(['message' => 'Gagal menyimpan gambar'], 500);
                }
                $validated['gambar_treatment'] = $path;
            }
    
            // Update data
            $treatment->update($validated);
    
            return response()->json([
                'message' => 'Treatment berhasil diperbarui',
                'data'    => $treatment,
            ], 200);
    
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi data gagal', 'errors' => $e->errors()], 422);
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
    
            // Hapus gambar jika ada
            if (!empty($treatment->gambar_treatment)) {
                $imagePath = "public/{$treatment->gambar_treatment}"; // Pastikan path benar
                Log::info("Mencoba menghapus gambar: " . $imagePath);

                if (Storage::exists($imagePath)) {
                    Storage::delete($imagePath);
                    Log::info("Gambar berhasil dihapus.");
                } else {
                    Log::warning("Gambar tidak ditemukan di storage.");
                }
            }
    
            // Hapus data treatment dari database
            $treatment->delete();
    
            return response()->json(['message' => 'Treatment dan gambar berhasil dihapus'], 200);
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
