<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    /**
     * Mendapatkan semua favorit user
     */
    public function getUserFavorites($userId)
    {
        try {
            // Validasi user
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['message' => 'User tidak ditemukan'], 404);
            }

            // Ambil favorit
            $favorites = [
                'doctors' => Favorite::with('dokter')
                    ->where('id_user', $userId)
                    ->whereNotNull('id_dokter')
                    ->get()
                    ->pluck('dokter'),

                'products' => Favorite::with('produk')
                    ->where('id_user', $userId)
                    ->whereNotNull('id_produk')
                    ->get()
                    ->pluck('produk'),

                'treatments' => Favorite::with('treatment')
                    ->where('id_user', $userId)
                    ->whereNotNull('id_treatment')
                    ->get()
                    ->pluck('treatment')
            ];

            return response()->json([
                'message' => 'Daftar favorit user berhasil diambil',
                'data' => $favorites
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil daftar favorit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan dokter favorit user
     */
    public function getFavoriteDoctors($userId)
    {
        try {
            $doctors = Favorite::with('dokter')
                ->where('id_user', $userId)
                ->whereNotNull('id_dokter')
                ->get()
                ->pluck('dokter');

            return response()->json([
                'message' => 'Daftar dokter favorit',
                'data' => $doctors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil dokter favorit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan produk favorit user
     */
    public function getFavoriteProducts($userId)
    {
        try {
            $products = Favorite::with('produk')
                ->where('id_user', $userId)
                ->whereNotNull('id_produk')
                ->get()
                ->pluck('produk');

            return response()->json([
                'message' => 'Daftar produk favorit',
                'data' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil produk favorit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan treatment favorit user
     */
    public function getFavoriteTreatments($userId)
    {
        try {
            $treatments = Favorite::with('treatment')
                ->where('id_user', $userId)
                ->whereNotNull('id_treatment')
                ->get()
                ->pluck('treatment');

            return response()->json([
                'message' => 'Daftar treatment favorit',
                'data' => $treatments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil treatment favorit',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
