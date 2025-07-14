<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\BookingTreatment;
use App\Models\PembelianProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\MidtransService;
use Midtrans\Config;
use Midtrans\Notification;

class PembayaranMidtransController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;

        // Tambahkan konfigurasi Midtrans untuk notification
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = config('midtrans.is_sanitized', true);
        Config::$is3ds = config('midtrans.is_3ds', true);
    }
    /**
     * Membuat Snap URL pembayaran untuk treatment
     */
    public function createTreatmentPayment(Request $request)
    {
        $request->validate([
            'id_booking_treatment' => 'required|exists:tb_booking_treatment,id_booking_treatment',
        ]);

        DB::beginTransaction();
        try {
            $booking = BookingTreatment::with('user', 'detailBooking.treatmentDetail', 'treatment')
                ->findOrFail($request->id_booking_treatment);

            // Cek apakah sudah ada pembayaran untuk booking ini
            $existingPayment = Pembayaran::where('id_booking_treatment', $booking->id_booking_treatment)->first();
            if ($existingPayment) {
                if ($existingPayment->status_pembayaran == 'Berhasil') {
                    return response()->json([
                        'message' => 'Treatment ini sudah dibayar',
                    ], 400);
                }
                $pembayaran = $existingPayment;
            } else {
                $pembayaran = Pembayaran::create([
                    'id_booking_treatment' => $booking->id_booking_treatment,
                    'id_penjualan_produk' => null,
                    'status_pembayaran' => 'Pending',
                    'metode_pembayaran' => 'Non Tunai',
                    'waktu_pembayaran' => null,
                    'gross_amount' => $booking->harga_akhir_treatment
                ]);
            }

            $snapData = $this->midtransService->createSnapUrlTreatment($booking, $pembayaran);

            if (!$snapData || !isset($snapData['redirect_url'])) {
                throw new \Exception('Gagal membuat Snap URL pembayaran');
            }

            $pembayaran->update([
                'snap_token' => $snapData['token'],
                'snap_url' => $snapData['redirect_url'],
                'order_id' => $snapData['order_id']
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Snap URL pembayaran treatment berhasil dibuat',
                'data' => [
                    'id_pembayaran' => $pembayaran->id_pembayaran,
                    'order_id' => $snapData['order_id'],
                    'snap_token' => $snapData['token'],
                    'snap_url' => $snapData['redirect_url'],
                    'gross_amount' => $pembayaran->gross_amount,
                    'status_pembayaran' => $pembayaran->status_pembayaran,
                    'payment_for' => 'treatment',
                    'booking_data' => [
                        'id_booking_treatment' => $booking->id_booking_treatment,
                        'tanggal_treatment' => $booking->tanggal_treatment,
                        'waktu_mulai' => $booking->waktu_mulai,
                        'treatment_name' => $booking->treatment->nama_treatment ?? 'Treatment'
                    ]
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error membuat Snap URL treatment: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Gagal membuat Snap URL pembayaran treatment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Membuat Snap URL pembayaran untuk produk - DISESUAIKAN DENGAN MODEL
     */
    public function createProductPayment(Request $request)
    {
        $request->validate([
            'id_penjualan_produk' => 'required|exists:tb_penjualan_produk,id_penjualan_produk',
        ]);

        DB::beginTransaction();
        try {
            Log::info('Request pembayaran produk', $request->all());

            // Load penjualan dengan relasi yang sesuai model
            $penjualan = PembelianProduk::with('user', 'detailPembelian.produk')
                ->findOrFail($request->id_penjualan_produk);

            Log::info('Data penjualan ditemukan', [
                'id' => $penjualan->id_penjualan_produk,
                'harga_akhir' => $penjualan->harga_akhir,
                'harga_total' => $penjualan->harga_total,
                'potongan_harga' => $penjualan->potongan_harga,
                'user_id' => $penjualan->id_user,
                'detail_count' => $penjualan->detailPembelian->count()
            ]);

            // Cek apakah sudah ada pembayaran untuk penjualan ini
            $existingPayment = Pembayaran::where('id_penjualan_produk', $penjualan->id_penjualan_produk)->first();
            if ($existingPayment) {
                if ($existingPayment->status_pembayaran == 'Berhasil') {
                    return response()->json([
                        'message' => 'Produk ini sudah dibayar',
                    ], 400);
                }
                $pembayaran = $existingPayment;
                Log::info('Pembayaran sudah ada, akan diupdate', [
                    'id_pembayaran' => $pembayaran->id_pembayaran,
                    'status' => $pembayaran->status_pembayaran
                ]);
            } else {
                $pembayaran = Pembayaran::create([
                    'id_booking_treatment' => null,
                    'id_penjualan_produk' => $penjualan->id_penjualan_produk,
                    'status_pembayaran' => 'Pending',
                    'metode_pembayaran' => 'Non Tunai',
                    'waktu_pembayaran' => null,
                    'gross_amount' => $penjualan->harga_akhir,
                ]);

                Log::info('Pembayaran baru dibuat', [
                    'id_pembayaran' => $pembayaran->id_pembayaran
                ]);
            }

            // Buat Snap URL untuk produk
            $snapData = $this->midtransService->createSnapUrlProduk($penjualan, $pembayaran);

            if (!$snapData || !isset($snapData['redirect_url'])) {
                Log::error('Gagal membuat Snap URL, tidak ada data yang dikembalikan dari service');
                throw new \Exception('Gagal membuat Snap URL pembayaran');
            }

            // Update pembayaran dengan snap token dan URL
            $pembayaran->update([
                'snap_token' => $snapData['token'],
                'snap_url' => $snapData['redirect_url'],
                'order_id' => $snapData['order_id']
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Snap URL pembayaran produk berhasil dibuat',
                'data' => [
                    'id_pembayaran' => $pembayaran->id_pembayaran,
                    'order_id' => $snapData['order_id'],
                    'snap_token' => $snapData['token'],
                    'snap_url' => $snapData['redirect_url'],
                    'gross_amount' => $pembayaran->gross_amount,
                    'status_pembayaran' => $pembayaran->status_pembayaran,
                    'payment_for' => 'product',
                    'product_data' => [
                        'id_penjualan_produk' => $penjualan->id_penjualan_produk,
                        'harga_total' => $penjualan->harga_total,
                        'potongan_harga' => $penjualan->potongan_harga,
                        'harga_akhir' => $penjualan->harga_akhir,
                        'total_qty' => $penjualan->detailPembelian->sum('jumlah_produk'),
                        'total_items' => $penjualan->detailPembelian->count()
                    ]
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error membuat Snap URL produk: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Gagal membuat Snap URL pembayaran produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * TAMBAHAN: Handle Midtrans notification webhook
     */
    /**
     * PERBAIKI: Handle Midtrans notification webhook
     */
    public function handleNotification(Request $request)
    {
        try {
            Log::info('Midtrans notification received', [
                'body' => $request->all(),
                'headers' => $request->headers->all()
            ]);

            // Buat notification object dari Midtrans
            $notification = new Notification();

            // Extract data dari notification
            $orderId = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $transactionId = $notification->transaction_id;
            $paymentType = $notification->payment_type;
            $grossAmount = $notification->gross_amount;
            $fraudStatus = isset($notification->fraud_status) ? $notification->fraud_status : null;

            Log::info('Notification parsed', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
                'transaction_id' => $transactionId,
                'payment_type' => $paymentType,
                'gross_amount' => $grossAmount,
                'fraud_status' => $fraudStatus
            ]);

            // Cari pembayaran berdasarkan order_id
            $pembayaran = Pembayaran::where('order_id', $orderId)->first();

            if (!$pembayaran) {
                Log::error('Pembayaran not found', [
                    'order_id' => $orderId,
                    'available_orders' => Pembayaran::whereNotNull('order_id')->pluck('order_id')->toArray()
                ]);
                return response()->json(['message' => 'Payment not found'], 404);
            }

            Log::info('Payment found', [
                'id_pembayaran' => $pembayaran->id_pembayaran,
                'current_status' => $pembayaran->status_pembayaran
            ]);

            DB::beginTransaction();

            // Siapkan data update
            $updateData = [
                'transaction_id' => $transactionId,
                'transaction_status' => $transactionStatus,
                'payment_type' => $paymentType,
                'midtrans_response' => json_encode($request->all()),
            ];

            // Extract VA number jika ada
            if (isset($notification->va_numbers) && is_array($notification->va_numbers)) {
                $vaNumber = $notification->va_numbers[0]->va_number ?? null;
                $bank = $notification->va_numbers[0]->bank ?? null;

                if ($vaNumber) {
                    $updateData['va_number'] = $vaNumber;
                    $updateData['bank'] = $bank;
                }
            } elseif (isset($notification->permata_va_number)) {
                $updateData['va_number'] = $notification->permata_va_number;
                $updateData['bank'] = 'permata';
            } elseif (isset($notification->bca_va_number)) {
                $updateData['va_number'] = $notification->bca_va_number;
                $updateData['bank'] = 'bca';
            } elseif (isset($notification->bill_key)) {
                $updateData['va_number'] = $notification->bill_key;
                $updateData['bank'] = 'mandiri';
            }

            // Tentukan status pembayaran berdasarkan transaction_status
            switch ($transactionStatus) {
                case 'capture':
                    if ($fraudStatus == 'accept') {
                        $updateData['status_pembayaran'] = 'Berhasil';
                        $updateData['waktu_pembayaran'] = now();
                        $updateData['metode_pembayaran'] = 'Non Tunai';
                    } else {
                        $updateData['status_pembayaran'] = 'Pending';
                    }
                    break;

                case 'settlement':
                    $updateData['status_pembayaran'] = 'Berhasil';
                    $updateData['waktu_pembayaran'] = now();
                    $updateData['metode_pembayaran'] = 'Non Tunai';
                    break;

                case 'pending':
                    $updateData['status_pembayaran'] = 'Pending';
                    break;

                case 'deny':
                case 'expire':
                case 'cancel':
                    $updateData['status_pembayaran'] = 'Gagal';
                    break;

                default:
                    $updateData['status_pembayaran'] = 'Pending';
                    break;
            }

            Log::info('Updating payment', [
                'id_pembayaran' => $pembayaran->id_pembayaran,
                'update_data' => $updateData
            ]);

            // Update pembayaran
            $pembayaran->update($updateData);

            // Jika pembayaran berhasil, update status terkait
            if ($updateData['status_pembayaran'] === 'Berhasil') {
                $this->handleSuccessfulPayment($pembayaran);
            }

            DB::commit();

            Log::info('Payment updated successfully', [
                'id_pembayaran' => $pembayaran->id_pembayaran,
                'new_status' => $updateData['status_pembayaran'],
                'transaction_id' => $transactionId
            ]);

            return response()->json([
                'message' => 'Notifikasi pembayaran berhasil diproses',
                'data' => [
                    'order_id' => $orderId,
                    'status_pembayaran' => $updateData['status_pembayaran'],
                    'transaction_id' => $transactionId,
                    'updated' => true
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error handling Midtrans notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Error processing notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PERBAIKI: Get payment detail tanpa middleware auth yang bermasalah
     */
    public function getPaymentDetail($id)
    {
        try {
            Log::info('Getting payment detail', ['id' => $id]);

            $pembayaran = Pembayaran::find($id);

            if (!$pembayaran) {
                return response()->json([
                    'message' => 'Pembayaran tidak ditemukan'
                ], 404);
            }

            // Manual load relations untuk menghindari error
            $data = [
                'id_pembayaran' => $pembayaran->id_pembayaran,
                'id_booking_treatment' => $pembayaran->id_booking_treatment,
                'id_penjualan_produk' => $pembayaran->id_penjualan_produk,
                'status_pembayaran' => $pembayaran->status_pembayaran,
                'metode_pembayaran' => $pembayaran->metode_pembayaran,
                'waktu_pembayaran' => $pembayaran->waktu_pembayaran,
                'gross_amount' => $pembayaran->gross_amount,
                'order_id' => $pembayaran->order_id,
                'transaction_id' => $pembayaran->transaction_id,
                'transaction_status' => $pembayaran->transaction_status,
                'payment_type' => $pembayaran->payment_type,
                'va_number' => $pembayaran->va_number,
                'bank' => $pembayaran->bank,
                'snap_token' => $pembayaran->snap_token,
                'snap_url' => $pembayaran->snap_url,
                'midtrans_response' => $pembayaran->midtrans_response,
                'created_at' => $pembayaran->created_at,
                'updated_at' => $pembayaran->updated_at,
            ];

            // Load related data jika diperlukan
            if ($pembayaran->id_booking_treatment) {
                $booking = BookingTreatment::find($pembayaran->id_booking_treatment);
                $data['booking_treatment'] = $booking;
            }

            if ($pembayaran->id_penjualan_produk) {
                $penjualan = PembelianProduk::find($pembayaran->id_penjualan_produk);
                $data['penjualan_produk'] = $penjualan;
            }

            return response()->json([
                'message' => 'Detail pembayaran berhasil ditemukan',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting payment detail', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Gagal mendapatkan detail pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DEBUG: Endpoint untuk cek payment status tanpa auth
     */
    public function debugPaymentDetail($id)
    {
        try {
            $pembayaran = Pembayaran::find($id);

            if (!$pembayaran) {
                return response()->json([
                    'message' => 'Pembayaran tidak ditemukan',
                    'available_payments' => Pembayaran::select('id_pembayaran', 'order_id', 'status_pembayaran')->get()
                ], 404);
            }

            return response()->json([
                'message' => 'Payment found',
                'data' => $pembayaran->toArray()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Method lainnya tetap sama...
    public function getApiInfo()
    {
        return response()->json([
            'status' => 'OK',
            'message' => 'Simple test endpoint working',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
