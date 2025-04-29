<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Komplain;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class KomplainController extends Controller
{
    public function index()
    {
        return response()->json(Komplain::with(['kompensasi', 'user'])->get());
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_user' => 'required|exists:tb_user,id_user',
                'teks_komplain' => 'nullable|string',
                'gambar_komplain.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',
                'gambar_bukti_transaksi' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            ]);

            // Simpan gambar komplain jika ada
            $gambarPaths = [];
            if ($request->hasFile('gambar_komplain')) {
                foreach ($request->file('gambar_komplain') as $file) {
                    $fileName = time() . '_komplain_' . $file->getClientOriginalName();
                    $path = $file->storeAs('komplain_images', $fileName, 'public');
                    if ($path) {
                        $gambarPaths[] = $path;
                    }
                }
            }

            // Simpan gambar bukti transaksi jika ada
            if ($request->hasFile('gambar_bukti_transaksi')) {
                $file = $request->file('gambar_bukti_transaksi');
                $fileName = time() . '_bukti_' . $file->getClientOriginalName();
                $path = $file->storeAs('bukti_transaksi_images', $fileName, 'public');
                if (!$path) {
                    return response()->json(['message' => 'Gagal menyimpan gambar bukti transaksi'], 500);
                }
                $validated['gambar_bukti_transaksi'] = $path;
            }

            // Simpan array gambar ke database dalam format JSON
            $validated['gambar_komplain'] = json_encode($gambarPaths);

            // Simpan data ke database
            $komplain = Komplain::create($validated);

            return response()->json([
                'message' => 'Komplain berhasil ditambahkan',
                'data' => $komplain,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi data gagal', 'errors' => $e->errors()], 422);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan komplain', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan yang tidak terduga', 'error' => $e->getMessage()], 500);
        }
    }


    // public function show($id)
    // {
    //     $komplain = Komplain::with(['kompensasi', 'user'])->findOrFail($id);
    //     return response()->json($komplain);
    // }

    public function update(Request $request, $id)
    {
        $komplain = Komplain::findOrFail($id);
        $data = $request->validate([
            'balasan_komplain' => 'nullable|string',
            'id_kompensasi' => 'nullable|exists:tb_kompensasi,id_kompensasi',
            'tanggal_berakhir_kompensasi' => 'nullable|date',
        ]);

        $komplain->update($data);
        return response()->json($komplain);
    }
}
