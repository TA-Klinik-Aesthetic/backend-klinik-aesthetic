<?php

namespace App\Services;

use App\Models\FcmToken;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class NotifikasiService
{
    /**
     * Firebase Cloud Messaging API URL
     * @var string
     */
    protected $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

    /**
     * Firebase Cloud Messaging Server Key
     * @var string
     */
    protected $serverKey;

    /**
     * Konstruktor
     */
    public function __construct()
    {
        $this->serverKey = config('services.fcm.server_key');
    }

    /**
     * Mengirim notifikasi ke satu user tertentu
     *
     * @param int $idUser ID user penerima
     * @param string $judul Judul notifikasi
     * @param string $pesan Isi pesan notifikasi
     * @param string $jenis Jenis notifikasi (treatment, konsultasi, produk, promo)
     * @param int|null $idReferensi ID referensi ke data terkait
     * @param string|null $gambar URL gambar untuk notifikasi
     * @return bool
     */
    public function sendToUser($idUser, $judul, $pesan, $jenis, $idReferensi = null, $gambar = null)
    {
        try {
            // Simpan notifikasi ke database
            $notifikasi = Notifikasi::create([
                'id_user' => $idUser,
                'judul' => $judul,
                'pesan' => $pesan,
                'jenis' => $jenis,
                'id_referensi' => $idReferensi,
                'status' => 'unread',
                'gambar' => $gambar,
                'tanggal_notifikasi' => now()
            ]);

            // Ambil token FCM dari user
            $tokens = FcmToken::where('id_user', $idUser)
                ->where('is_active', true)
                ->pluck('device_token')
                ->toArray();

            if (empty($tokens)) {
                Log::info("User ID {$idUser} tidak memiliki token FCM yang aktif");
                return false;
            }

            // Data untuk FCM
            $data = [
                'registration_ids' => $tokens,
                'notification' => [
                    'title' => $judul,
                    'body' => $pesan,
                    'sound' => 'default',
                    'badge' => '1',
                ],
                'data' => [
                    'id_notifikasi' => $notifikasi->id_notifikasi,
                    'jenis' => $jenis,
                    'id_referensi' => $idReferensi,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]
            ];

            // Tambahkan gambar jika ada
            if ($gambar) {
                $data['notification']['image'] = $gambar;
            }

            // Kirim ke FCM
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json'
            ])->post($this->fcmUrl, $data);

            // Log response
            Log::info('FCM Response untuk user ' . $idUser, [
                'response' => $response->json(),
                'status_code' => $response->status()
            ]);

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Error saat mengirim notifikasi: ' . $e->getMessage(), [
                'exception' => $e,
                'id_user' => $idUser
            ]);
            return false;
        }
    }

    /**
     * Mengirim notifikasi ke semua user
     *
     * @param string $judul Judul notifikasi
     * @param string $pesan Isi pesan notifikasi
     * @param string $jenis Jenis notifikasi (treatment, konsultasi, produk, promo)
     * @param int|null $idReferensi ID referensi ke data terkait
     * @param string|null $gambar URL gambar untuk notifikasi
     * @return bool
     */
    public function sendToAllUsers($judul, $pesan, $jenis, $idReferensi = null, $gambar = null)
    {
        try {
            // Ambil semua user
            $users = User::all();
            $successCount = 0;

            foreach ($users as $user) {
                $result = $this->sendToUser(
                    $user->id_user,
                    $judul,
                    $pesan,
                    $jenis,
                    $idReferensi,
                    $gambar
                );

                if ($result) {
                    $successCount++;
                }
            }

            Log::info("Notifikasi berhasil dikirim ke {$successCount} dari {$users->count()} user");

            return true;
        } catch (Exception $e) {
            Log::error('Error saat mengirim notifikasi ke semua user: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return false;
        }
    }

    /**
     * Mengirim notifikasi untuk Treatment
     *
     * @param int $idUser ID user penerima
     * @param int $idBookingTreatment ID booking treatment
     * @param string $statusBaru Status baru dari booking treatment
     * @return bool
     */
    public function sendTreatmentNotification($idUser, $idBookingTreatment, $statusBaru)
    {
        $judul = "Status Treatment Diperbarui";
        $pesan = "Status booking treatment Anda telah berubah menjadi {$statusBaru}";
        return $this->sendToUser($idUser, $judul, $pesan, 'treatment', $idBookingTreatment);
    }

    /**
     * Mengirim notifikasi untuk Konsultasi
     *
     * @param int $idUser ID user penerima
     * @param int $idKonsultasi ID konsultasi
     * @param string $statusBaru Status baru dari konsultasi
     * @return bool
     */
    public function sendKonsultasiNotification($idUser, $idKonsultasi, $statusBaru)
    {
        $judul = "Status Konsultasi Diperbarui";
        $pesan = "Status konsultasi Anda telah berubah menjadi {$statusBaru}";
        return $this->sendToUser($idUser, $judul, $pesan, 'konsultasi', $idKonsultasi);
    }

    /**
     * Mengirim notifikasi untuk Pembelian Produk
     *
     * @param int $idUser ID user penerima
     * @param int $idPembelian ID pembelian produk
     * @param string $statusBaru Status baru dari pembelian
     * @return bool
     */
    public function sendPembelianNotification($idUser, $idPembelian, $statusBaru)
    {
        $judul = "Status Pembelian Diperbarui";
        $pesan = "Status pembelian produk Anda telah berubah menjadi {$statusBaru}";
        return $this->sendToUser($idUser, $judul, $pesan, 'produk', $idPembelian);
    }

    /**
     * Mengirim notifikasi untuk Promo Baru
     *
     * @param int $idPromo ID promo baru
     * @param string $namaPromo Nama promo
     * @param string|null $gambar URL gambar promo
     * @return bool
     */
    public function sendPromoNotification($idPromo, $namaPromo, $gambar = null)
    {
        $judul = "Promo Baru!";
        $pesan = "Ada promo baru: {$namaPromo}. Cek sekarang!";
        return $this->sendToAllUsers($judul, $pesan, 'promo', $idPromo, $gambar);
    }
}
