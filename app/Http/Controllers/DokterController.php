<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokter;
use App\Models\Favorite;

class DokterController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->query('id_user');
        $dokters = Dokter::all();

        if ($userId) {
            foreach ($dokters as $dokter) {
                $dokter->is_favorited = $dokter->isFavoritedBy($userId);
                $dokter->favorites_count = $dokter->getFavoritesCountAttribute();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Data Dokter',
            'data' => $dokters
        ]);
    }

    public function show($id, Request $request)
    {
        $userId = $request->query('id_user');
        $dokter = Dokter::findOrFail($id);

        if ($userId) {
            $dokter->is_favorited = $dokter->isFavoritedBy($userId);
            $dokter->favorites_count = $dokter->getFavoritesCountAttribute();
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Dokter',
            'data' => $dokter
        ]);
    }

    public function toggleFavorite(Request $request)
    {
        $request->validate([
            'id_user' => 'required|exists:tb_user,id_user',
            'id_dokter' => 'required|exists:tb_dokter,id_dokter'
        ]);

        $userId = $request->id_user;
        $dokterId = $request->id_dokter;

        $favorite = Favorite::where('id_user', $userId)
            ->where('id_dokter', $dokterId)
            ->first();

        if ($favorite) {
            // Jika sudah favorit, hapus dari favorit
            $favorite->delete();
            $message = 'Dokter dihapus dari favorit';
            $status = false;
        } else {
            // Jika belum favorit, tambahkan ke favorit
            Favorite::create([
                'id_user' => $userId,
                'id_dokter' => $dokterId
            ]);
            $message = 'Dokter ditambahkan ke favorit';
            $status = true;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_favorited' => $status
        ]);
    }
}
