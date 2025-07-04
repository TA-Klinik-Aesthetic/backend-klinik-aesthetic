<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class FcmTokenController extends Controller
{
    /**
     * Menyimpan token FCM baru
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_user' => 'required|exists:tb_user,id_user',
                'device_token' => 'required|string',
                'device_type' => 'nullable|string|in:android,ios,web',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Cek apakah token sudah ada
            $existingToken = FcmToken::where('id_user', $request->id_user)
                ->where('device_token', $request->device_token)
                ->first();

            if ($existingToken) {
                // Update status jika sudah ada
                $existingToken->update([
                    'is_active' => true,
                    'device_type' => $request->device_type
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Token berhasil diperbarui',
                    'data' => $existingToken
                ]);
            }

            // Buat token baru
            $fcmToken = FcmToken::create([
                'id_user' => $request->id_user,
                'device_token' => $request->device_token,
                'device_type' => $request->device_type,
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token berhasil disimpan',
                'data' => $fcmToken
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menonaktifkan token FCM
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_user' => 'required|exists:tb_user,id_user',
                'device_token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Cari token yang sesuai
            $token = FcmToken::where('id_user', $request->id_user)
                ->where('device_token', $request->device_token)
                ->first();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak ditemukan'
                ], 404);
            }

            // Nonaktifkan token
            $token->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Token berhasil dinonaktifkan'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menonaktifkan token',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
