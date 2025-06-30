<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\BookingTreatment;
use App\Models\PembelianProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\MidtransService;

class PembayaranMidtransController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Membuat token pembayaran untuk treatment
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

            // Cek apakah sudah ada pembayaran untuk booking ini
            $existingPayment = Pembayaran::where('id_booking_treatment', $booking->id_booking_treatment)->first();
            if ($existingPayment) {
                // Jika sudah ada pembayaran dengan status berhasil, kembalikan error
                if ($existingPayment->status_pembayaran == 'Berhasil') {
                    return response()->json([
                        'message' => 'Treatment ini sudah dibayar',
                    ], 400);
                }

                // Jika tidak berhasil, update token pembayaran
                $pembayaran = $existingPayment;
            } else {
                // Buat pembayaran baru
                $pembayaran = Pembayaran::create([
                    'id_booking_treatment' => $booking->id_booking_treatment,
                    'id_penjualan_produk' => null,
                    'status_pembayaran' => 'Pending',
                    'metode_pembayaran' => 'Non Tunai',
                    'waktu_pembayaran' => null,
                    'gross_amount' => $booking->harga_akhir_treatment
                ]);
            }

            // Buat token pembayaran untuk Flutter SDK
            $paymentData = $this->midtransService->createTransactionTokenTreatment($booking, $pembayaran);

            if (!$paymentData) {
                throw new \Exception('Gagal membuat token pembayaran');
            }

            DB::commit();

            return response()->json([
                'message' => 'Token pembayaran treatment berhasil dibuat',
                'data' => $pembayaran,
                'payment_data' => $paymentData
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error membuat pembayaran treatment: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Gagal membuat token pembayaran treatment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Membuat token pembayaran untuk produk
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

            // Cek apakah sudah ada pembayaran untuk penjualan ini
            $existingPayment = Pembayaran::where('id_penjualan_produk', $penjualan->id_penjualan_produk)->first();
            if ($existingPayment) {
                // Jika sudah ada pembayaran dengan status berhasil, kembalikan error
                if ($existingPayment->status_pembayaran == 'Berhasil') {
                    return response()->json([
                        'message' => 'Produk ini sudah dibayar',
                    ], 400);
                }

                // Jika tidak berhasil, update token pembayaran
                $pembayaran = $existingPayment;
            } else {
                // Buat pembayaran baru
                $pembayaran = Pembayaran::create([
                    'id_booking_treatment' => null,
                    'id_penjualan_produk' => $penjualan->id_penjualan_produk,
                    'status_pembayaran' => 'Pending',
                    'metode_pembayaran' => 'Non Tunai',
                    'waktu_pembayaran' => null,
                    'gross_amount' => $penjualan->harga_akhir
                ]);
            }

            // Buat token pembayaran untuk Flutter SDK
            $paymentData = $this->midtransService->createTransactionTokenProduk($penjualan, $pembayaran);

            if (!$paymentData) {
                throw new \Exception('Gagal membuat token pembayaran');
            }

            DB::commit();

            return response()->json([
                'message' => 'Token pembayaran produk berhasil dibuat',
                'data' => $pembayaran,
                'payment_data' => $paymentData
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error membuat pembayaran produk: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Gagal membuat token pembayaran produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menerima dan memproses notifikasi pembayaran dari Midtrans
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleNotification(Request $request)
    {
        try {
            $notificationBody = $request->all();

            Log::info('Notifikasi Midtrans diterima', $notificationBody);

            // Validasi data penting
            if (!isset($notificationBody['order_id']) || !isset($notificationBody['transaction_status'])) {
                return response()->json(['message' => 'Data notifikasi tidak lengkap'], 400);
            }

            $orderId = $notificationBody['order_id'];
            $transactionStatus = $notificationBody['transaction_status'];
            $fraudStatus = $notificationBody['fraud_status'] ?? null;
            $transactionId = $notificationBody['transaction_id'] ?? null;
            $paymentType = $notificationBody['payment_type'] ?? null;
            $vaNumber = $notificationBody['va_numbers'][0]['va_number'] ?? null;
            $bank = $notificationBody['va_numbers'][0]['bank'] ?? null;
            $grossAmount = $notificationBody['gross_amount'] ?? null;

            // Ekstrak ID pembayaran dari order_id (format: TRT-{id_pembayaran}-{timestamp} atau PRD-{id_pembayaran}-{timestamp})
            $orderParts = explode('-', $orderId);
            if (count($orderParts) < 2) {
                return response()->json(['message' => 'Format order ID tidak valid'], 400);
            }

            $idPembayaran = $orderParts[1];

            // Cari pembayaran
            $pembayaran = Pembayaran::find($idPembayaran);
            if (!$pembayaran) {
                return response()->json(['message' => 'Pembayaran tidak ditemukan'], 404);
            }

            // Update data pembayaran dengan informasi dari Midtrans
            $pembayaran->transaction_id = $transactionId;
            $pembayaran->transaction_status = $transactionStatus;
            $pembayaran->payment_type = $paymentType;
            $pembayaran->va_number = $vaNumber;
            $pembayaran->bank = $bank;
            $pembayaran->gross_amount = $grossAmount;
            $pembayaran->midtrans_response = json_encode($notificationBody);

            // Update status pembayaran berdasarkan transaction_status dari Midtrans
            if (in_array($transactionStatus, ['settlement', 'capture']) && $fraudStatus != 'deny') {
                $pembayaran->status_pembayaran = 'Berhasil';
                $pembayaran->waktu_pembayaran = now();

                // Jika pembayaran berhasil, update data terkait
                if ($pembayaran->id_booking_treatment) {
                    $booking = BookingTreatment::find($pembayaran->id_booking_treatment);
                    if ($booking) {
                        $booking->status_booking = 'Terkonfirmasi';
                        $booking->save();
                    }
                }
            } elseif ($transactionStatus == 'pending') {
                $pembayaran->status_pembayaran = 'Pending';
            } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire']) || $fraudStatus == 'deny') {
                $pembayaran->status_pembayaran = 'Gagal';
            }

            // Sesuaikan metode pembayaran berdasarkan payment_type
            if ($paymentType) {
                switch ($paymentType) {
                    case 'bank_transfer':
                        $pembayaran->metode_pembayaran = 'Virtual Account';
                        break;
                    case 'qris':
                        $pembayaran->metode_pembayaran = 'QRIS';
                        break;
                    case 'gopay':
                    case 'shopeepay':
                        $pembayaran->metode_pembayaran = 'E-Wallet';
                        break;
                    default:
                        $pembayaran->metode_pembayaran = 'Non Tunai';
                }
            }

            $pembayaran->save();

            return response()->json(['status' => 'OK']);

        } catch (\Exception $e) {
            Log::error('Error saat memproses notifikasi Midtrans: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Error saat memproses notifikasi pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memeriksa status pembayaran berdasarkan order_id
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
        ]);

        try {
            $orderId = $request->order_id;

            // Ekstrak ID pembayaran dari order_id
            $orderParts = explode('-', $orderId);
            if (count($orderParts) < 2) {
                return response()->json([
                    'message' => 'Format order ID tidak valid'
                ], 400);
            }

            $idPembayaran = $orderParts[1];

            // Cari pembayaran
            $pembayaran = Pembayaran::with(['bookingTreatment', 'penjualanProduk'])
                ->findOrFail($idPembayaran);

            // Format response
            $responseData = [
                'id_pembayaran' => $pembayaran->id_pembayaran,
                'order_id' => $pembayaran->order_id,
                'status_pembayaran' => $pembayaran->status_pembayaran,
                'metode_pembayaran' => $pembayaran->metode_pembayaran,
                'waktu_pembayaran' => $pembayaran->waktu_pembayaran,
                'payment_type' => $pembayaran->payment_type,
                'gross_amount' => $pembayaran->gross_amount,
                'transaction_id' => $pembayaran->transaction_id,
                'transaction_status' => $pembayaran->transaction_status,
            ];

            // Tambahkan data spesifik berdasarkan jenis pembayaran
            if ($pembayaran->id_booking_treatment) {
                $responseData['payment_for'] = 'treatment';
                $responseData['id_booking_treatment'] = $pembayaran->id_booking_treatment;
                if ($pembayaran->bookingTreatment) {
                    $responseData['treatment_data'] = [
                        'tanggal_treatment' => $pembayaran->bookingTreatment->tanggal_treatment,
                        'waktu_mulai' => $pembayaran->bookingTreatment->waktu_mulai,
                        'waktu_selesai' => $pembayaran->bookingTreatment->waktu_selesai,
                        'status_booking' => $pembayaran->bookingTreatment->status_booking
                    ];
                }
            } elseif ($pembayaran->id_penjualan_produk) {
                $responseData['payment_for'] = 'product';
                $responseData['id_penjualan_produk'] = $pembayaran->id_penjualan_produk;
            }

            return response()->json($responseData);

        } catch (\Exception $e) {
            Log::error('Error saat memeriksa status pembayaran: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Gagal memeriksa status pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan detail pembayaran berdasarkan ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail($id)
    {
        try {
            $pembayaran = Pembayaran::with(['bookingTreatment.user', 'penjualanProduk.user'])
                ->findOrFail($id);

            return response()->json([
                'message' => 'Detail pembayaran berhasil diambil',
                'data' => $pembayaran
            ]);
        } catch (\Exception $e) {
            Log::error('Error saat mengambil detail pembayaran: ' . $e->getMessage());

            return response()->json([
                'message' => 'Gagal mengambil detail pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getApiInfo()
    {
        return response()->json([
        'status' => 'OK',
        'message' => 'Simple test endpoint working',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    }

    public function getAvailablePaymentMethods()
    {
        try {
            $methods = [
                [
                    'id' => 'bca',
                    'name' => 'BCA Virtual Account',
                    'group' => 'bank_transfer',
                    'image' => 'bca.png',
                    'description' => 'Bayar dari BCA melalui Virtual Account'
                ],
                [
                    'id' => 'bni',
                    'name' => 'BNI Virtual Account',
                    'group' => 'bank_transfer',
                    'image' => 'bni.png',
                    'description' => 'Bayar dari BNI melalui Virtual Account'
                ],
                [
                    'id' => 'bri',
                    'name' => 'BRI Virtual Account',
                    'group' => 'bank_transfer',
                    'image' => 'bri.png',
                    'description' => 'Bayar dari BRI melalui Virtual Account'
                ],
                [
                    'id' => 'mandiri',
                    'name' => 'Mandiri Bill Payment',
                    'group' => 'bank_transfer',
                    'image' => 'mandiri.png',
                    'description' => 'Bayar dari Mandiri melalui Bill Payment'
                ],
                [
                    'id' => 'gopay',
                    'name' => 'GoPay',
                    'group' => 'e_wallet',
                    'image' => 'gopay.png',
                    'description' => 'Bayar dengan GoPay'
                ],
                [
                    'id' => 'shopeepay',
                    'name' => 'ShopeePay',
                    'group' => 'e_wallet',
                    'image' => 'shopeepay.png',
                    'description' => 'Bayar dengan ShopeePay'
                ],
                [
                    'id' => 'qris',
                    'name' => 'QRIS',
                    'group' => 'qris',
                    'image' => 'qris.png',
                    'description' => 'Bayar dengan QRIS (Dana, OVO, LinkAja, dll)'
                ],
            ];

            return response()->json([
                'message' => 'Daftar metode pembayaran berhasil diambil',
                'data' => $methods
            ]);
        } catch (\Exception $e) {
            Log::error('Error saat mengambil metode pembayaran: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'message' => 'Gagal mengambil daftar metode pembayaran',
                'error' => 'Terjadi kesalahan di server'
            ], 500);
        }
    }

    /**
     * Mendapatkan daftar semua pembayaran yang menggunakan Midtrans
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request)
    {
        try {
            $query = Pembayaran::query();

            // Filter berdasarkan status pembayaran jika ada
            if ($request->has('status')) {
                $query->where('status_pembayaran', $request->status);
            }

            // Filter berdasarkan metode pembayaran
            if ($request->has('metode')) {
                $query->where('metode_pembayaran', $request->metode);
            }

            // Filter berdasarkan jenis pembayaran (treatment atau produk)
            if ($request->has('jenis')) {
                if ($request->jenis == 'treatment') {
                    $query->whereNotNull('id_booking_treatment');
                } elseif ($request->jenis == 'product') {
                    $query->whereNotNull('id_penjualan_produk');
                }
            }

            // Filter hanya pembayaran Midtrans (tidak tunai)
            $query->where('metode_pembayaran', '!=', 'Tunai')
                  ->whereNotNull('order_id');

            // Pagination
            $perPage = $request->per_page ?? 15;
            $pembayaranList = $query->with(['bookingTreatment.user', 'penjualanProduk.user'])
                                   ->orderBy('created_at', 'desc')
                                   ->paginate($perPage);

            return response()->json([
                'message' => 'Daftar pembayaran berhasil diambil',
                'data' => $pembayaranList
            ]);
        } catch (\Exception $e) {
            Log::error('Error saat mengambil daftar pembayaran: ' . $e->getMessage());

            return response()->json([
                'message' => 'Gagal mengambil daftar pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
