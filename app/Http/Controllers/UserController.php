<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log; // <- Tambahkan ini
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{
    public function index()
    {
        $users = User::all(); // Mengambil semua data pengguna
        return response()->json([
            'success' => true,
            'message' => 'Data Pengguna',
            'data' => $users
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Data Pengguna yang Sedang Login',
            'data' => $request->user(), // Data pengguna yang sedang login
        ]);
    }

    public function show($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail Data Pengguna',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {

            Log::info('request()->all():', $request->all());

            $validatedData = $request->validate([
                'nama_user' => 'nullable|string|max:255',
                'no_telp' => 'nullable|string',
                'email' => 'nullable|string|email',
                'tanggal_lahir' => 'nullable|date',
                'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            ]);

            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                ], 404);
            }

            if ($request->hasFile('foto_profil')) {
                if ($request->hasFile('foto_profil')) {
                    Log::info('File terdeteksi:', [$request->file('foto_profil')]);
                }
                $file = $request->file('foto_profil');
            
                // Buat nama unik
                $fileName = time() . '_' . $file->getClientOriginalName();
            
                // Simpan ke folder storage/app/public/profil_user/
                $path = $file->storeAs('profil_user', $fileName, 'public');
            
                if (!$path) {
                    return response()->json(['success' => false, 'message' => 'Gagal menyimpan foto profil'], 500);
                }
            
                $validatedData['foto_profil'] = $path;
            }

            $user->update($validatedData);
            $user = User::find($user->id_user); // paksa ambil ulang
            
            

            return response()->json([
                'success' => true,
                'message' => 'Data pengguna berhasil diperbarui',
                'data' => $user
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }


    // public function update(Request $request, $id)
    // {
    //     try {
    //         $user = User::find($id);

    //         if (!$user) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'User tidak ditemukan',
    //             ], 404);
    //         }

    //         // Langsung ambil input dari request
    //         $data = $request->only(['nama_user', 'no_telp', 'email', 'tanggal_lahir']);

    //         $user->update($data);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Data pengguna berhasil diperbarui',
    //             'data' => $user
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
}
