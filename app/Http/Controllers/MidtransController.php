<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\BookingTreatment;
use App\Models\PembelianProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Buat token pembayaran Midtrans untuk treatment
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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

            // Check if payment already exists
            $existingPayment = Pembayaran::where('id_booking_treatment', $booking->id_booking_treatment)->first();
            if ($existingPayment) {
                // Jika sudah ada pembayaran tapi bukan Midtrans, buat pembayaran baru
                if (!$existingPayment->snap_token) {
                    $pembayaran = Pembayaran::create([
                        'id_booking_treatment' => $booking->id_booking_treatment,
                        'id_penjualan_produk' => null,
                        'status_pembayaran' => 'Pending',
                        'metode_pembayaran' => 'Non Tunai',
                    ]);
                } else {
                    // Jika sudah ada pembayaran Midtrans, gunakan yang ada
                    $pembayaran = $existingPayment;
                }
            } else {
                // Buat pembayaran baru
                $pembayaran = Pembayaran::create([
                    'id_booking_treatment' => $booking->id_booking_treatment,
                    'id_penjualan_produk' => null,
                    'status_pembayaran' => 'Pending',
                    'metode_pembayaran' => 'Non Tunai',
                ]);
            }

            // Create Midtrans Transaction Token for SDK
            $sdkData = $this->midtransService->createTransactionTokenTreatment($booking, $pembayaran);

            if (!$sdkData) {
                throw new \Exception('Failed to create payment token');
            }

            DB::commit();

            return response()->json([
                'message' => 'Token pembayaran treatment berhasil dibuat',
                'data' => $pembayaran,
                'sdk_data' => $sdkData
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating treatment payment: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error creating treatment payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buat token pembayaran Midtrans untuk produk
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createProductPayment(Request $request)
    {
        $request->validate([
            'id_penjualan_produk' => 'required|exists:tb_penjualan_produk,id_penjualan_produk',
        ]);

        DB::beginTransaction();
        try {
            $penjualan = PembelianProduk::with('user', 'detailPembelian.produk')
                ->findOrFail($request->id_penjualan_produk);

            // Check if payment already exists
            $existingPayment = Pembayaran::where('id_penjualan_produk', $penjualan->id_penjualan_produk)->first();
            if ($existingPayment) {
                // Jika sudah ada pembayaran tapi bukan Midtrans, buat pembayaran baru
                if (!$existingPayment->snap_token) {
                    $pembayaran = Pembayaran::create([
                        'id_booking_treatment' => null,
                        'id_penjualan_produk' => $penjualan->id_penjualan_produk,
                        'status_pembayaran' => 'Pending',
                        'metode_pembayaran' => 'Non Tunai',
                    ]);
                } else {
                    // Jika sudah ada pembayaran Midtrans, gunakan yang ada
                    $pembayaran = $existingPayment;
                }
            } else {
                // Buat pembayaran baru
                $pembayaran = Pembayaran::create([
                    'id_booking_treatment' => null,
                    'id_penjualan_produk' => $penjualan->id_penjualan_produk,
                    'status_pembayaran' => 'Pending',
                    'metode_pembayaran' => 'Non Tunai',
                ]);
            }

            // Create Midtrans Transaction Token for SDK
            $sdkData = $this->midtransService->createTransactionTokenProduk($penjualan, $pembayaran);

            if (!$sdkData) {
                throw new \Exception('Failed to create payment token');
            }

            DB::commit();

            return response()->json([
                'message' => 'Token pembayaran produk berhasil dibuat',
                'data' => $pembayaran,
                'sdk_data' => $sdkData
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating product payment: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error creating product payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle notification from Midtrans
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleNotification(Request $request)
    {
        try {
            $notificationBody = $request->all();

            // Sample notification handling
            $orderId = $notificationBody['order_id'];
            $transactionStatus = $notificationBody['transaction_status'];
            $paymentType = $notificationBody['payment_type'];
            $transactionId = $notificationBody['transaction_id'];
            $fraudStatus = $notificationBody['fraud_status'] ?? null;

            // Parse order ID to get payment ID
            $parts = explode('-', $orderId);
            if (count($parts) < 2) {
                return response()->json(['error' => 'Invalid order ID format'], 400);
            }

            $idPembayaran = $parts[1];
            $pembayaran = Pembayaran::find($idPembayaran);

            if (!$pembayaran) {
                return response()->json(['error' => 'Payment not found'], 404);
            }

            // Map payment type to readable format for our database
            $metodePembayaran = $this->mapPaymentType($paymentType);

            // Update payment information
            $pembayaran->payment_type = $paymentType;
            $pembayaran->transaction_id = $transactionId;
            $pembayaran->payment_details = $notificationBody;
            $pembayaran->metode_pembayaran = $metodePembayaran;

            // Update payment status based on Midtrans status
            if (in_array($transactionStatus, ['settlement', 'capture']) && $fraudStatus != 'deny') {
                $pembayaran->status_pembayaran = 'Berhasil';
                $pembayaran->waktu_pembayaran = now();
            } elseif ($transactionStatus == 'pending') {
                $pembayaran->status_pembayaran = 'Pending';
            } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire']) || $fraudStatus == 'deny') {
                $pembayaran->status_pembayaran = 'Gagal';
            }

            $pembayaran->save();

            // Log notification for debugging
            Log::info('Midtrans notification received', [
                'order_id' => $orderId,
                'status' => $transactionStatus,
                'payment_type' => $paymentType,
                'payment_status' => $pembayaran->status_pembayaran
            ]);

            return response()->json(['status' => 'OK']);

        } catch (\Exception $e) {
            Log::error('Error processing Midtrans notification: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error processing notification',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check payment status
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPaymentStatus(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|string'
            ]);

            $orderId = $request->order_id;

            // Parse order ID to get payment ID
            $parts = explode('-', $orderId);
            if (count($parts) < 2) {
                return response()->json(['message' => 'Invalid order ID format'], 400);
            }

            $idPembayaran = $parts[1];
            $pembayaran = Pembayaran::find($idPembayaran);

            if (!$pembayaran) {
                return response()->json(['message' => 'Payment not found'], 404);
            }

            $responseData = [
                'status' => $pembayaran->status_pembayaran,
                'payment_type' => $pembayaran->payment_type,
                'order_id' => $pembayaran->order_id,
                'transaction_id' => $pembayaran->transaction_id,
                'waktu_pembayaran' => $pembayaran->waktu_pembayaran
            ];

            // Include additional info if it's a treatment payment
            if ($pembayaran->id_booking_treatment) {
                $responseData['payment_for'] = 'treatment';
                $responseData['id_booking_treatment'] = $pembayaran->id_booking_treatment;
            }

            // Include additional info if it's a product payment
            if ($pembayaran->id_penjualan_produk) {
                $responseData['payment_for'] = 'product';
                $responseData['id_penjualan_produk'] = $pembayaran->id_penjualan_produk;
            }

            return response()->json($responseData);

        } catch (\Exception $e) {
            Log::error('Error checking payment status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error checking payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to map payment types from Midtrans to our database format
     *
     * @param string $paymentType
     * @return string
     */
    private function mapPaymentType($paymentType)
    {
        switch ($paymentType) {
            case 'qris':
                return 'QRIS';
            case 'gopay':
            case 'shopeepay':
                return 'E-Wallet';
            case 'bank_transfer':
            case 'echannel':
            case 'bca_va':
            case 'bni_va':
            case 'bri_va':
            case 'permata_va':
                return 'Virtual Account';
            case 'credit_card':
                return 'Kartu Kredit';
            default:
                return 'Non Tunai';
        }
    }
}
