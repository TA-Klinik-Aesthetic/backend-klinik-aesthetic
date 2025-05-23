<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

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
            // Validasi input
            $validatedData = $request->validate([
                'nama_user' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255|unique:tb_user,email,'.$id.',id_user',
                'password' => 'sometimes|string|min:8',
                'no_telp' => 'sometimes|string|unique:tb_user,no_telp,'.$id.',id_user',
                'tanggal_lahir' => 'sometimes|date',
                'role' => 'sometimes|string|in:pelanggan,dokter,beautician,front office,kasir,admin',
                // Tambahkan field lain sesuai kebutuhan
            ]);

            // Cari user berdasarkan ID
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                ], 404);
            }

            // Update password jika ada di request
            if (isset($validatedData['password'])) {
                $validatedData['password'] = bcrypt($validatedData['password']);
            }

            // Update user
            $user->update($validatedData);

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
}
