<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class NotifikasiController extends Controller
{
    protected $notifikasiService;

    /**
     * Konstruktor
     */
    public function __construct(NotifikasiService $notifikasiService)
    {
        $this->notifikasiService = $notifikasiService;
    }

    /**
     * Mendapatkan semua notifikasi user
     *
     * @param int $idUser ID User
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserNotifications($idUser)
    {
        try {
            $notifications = Notifikasi::where('id_user', $idUser)
                ->orderBy('tanggal_notifikasi', 'desc')
                ->get();

            $unreadCount = Notifikasi::where('id_user', $idUser)
                ->where('status', 'unread')
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil diambil',
                'data' => [
                    'notifications' => $notifications,
                    'unread_count' => $unreadCount
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menandai notifikasi sebagai dibaca
     *
     * @param Request $request
     * @param int $id ID Notifikasi
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $notification = Notifikasi::find($id);

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notifikasi tidak ditemukan'
                ], 404);
            }

            $notification->update(['status' => 'read']);

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi ditandai sebagai dibaca',
                'data' => $notification
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menandai semua notifikasi user sebagai dibaca
     *
     * @param Request $request
     * @param int $idUser ID User
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request, $idUser)
    {
        try {
            Notifikasi::where('id_user', $idUser)
                ->where('status', 'unread')
                ->update(['status' => 'read']);

            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi ditandai sebagai dibaca'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengirim notifikasi test ke user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendTestNotification(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_user' => 'required|exists:tb_user,id_user',
                'judul' => 'required|string',
                'pesan' => 'required|string',
                'jenis' => 'required|string|in:treatment,konsultasi,produk,promo',
                'id_referensi' => 'nullable|integer',
                'gambar' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 400);
            }

            $result = $this->notifikasiService->sendToUser(
                $request->id_user,
                $request->judul,
                $request->pesan,
                $request->jenis,
                $request->id_referensi,
                $request->gambar
            );

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notifikasi test berhasil dikirim'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Notifikasi gagal dikirim'
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim notifikasi test',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
