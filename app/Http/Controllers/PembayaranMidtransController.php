<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\BookingTreatment;
use App\Models\PembelianProduk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\MidtransService;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Transaction;

class PembayaranMidtransController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;

        // Tambahkan konfigurasi Midtrans untuk notification - TAMBAH CLIENT KEY
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = config('midtrans.is_sanitized', true);
        Config::$is3ds = config('midtrans.is_3ds', true);
    }

    // ...existing createTreatmentPayment method tetap sama...
    /**
     * Create Midtrans Snap for Treatment booking
     * body: { "id_booking_treatment": <int> }
     */
    public function createTreatmentPayment(Request $request)
    {
        $request->validate([
            'id_booking_treatment' => 'required|integer|exists:tb_booking_treatment,id_booking_treatment',
        ]);

        DB::beginTransaction();
        try {
            // Ambil booking + user (tanpa relasi Eloquent pun aman)
            $booking = BookingTreatment::lockForUpdate()
                ->where('id_booking_treatment', $request->id_booking_treatment)
                ->firstOrFail();

            $user = null;
            if (isset($booking->id_user)) {
                $user = User::find($booking->id_user);
            }

            // Hitung nominal akhir (fallback jika kolom null)
            $hargaTotal      = (float) ($booking->harga_total ?? 0);
            $potongan        = (float) ($booking->potongan_harga ?? 0);
            $pajak           = (float) ($booking->besaran_pajak ?? 0);
            $hargaAkhirField = (float) ($booking->harga_akhir_treatment ?? 0);

            $grossAmount = $hargaAkhirField > 0
                ? $hargaAkhirField
                : max(0, $hargaTotal - $potongan + $pajak);

            if ($grossAmount <= 0) {
                return response()->json([
                    'message' => 'Nominal pembayaran treatment tidak valid (<= 0).',
                    'data' => [
                        'harga_total' => $booking->harga_total,
                        'potongan_harga' => $booking->potongan_harga,
                        'besaran_pajak' => $booking->besaran_pajak,
                        'harga_akhir_treatment' => $booking->harga_akhir_treatment,
                    ],
                ], 422);
            }

            // Cek pembayaran existing
            $pembayaran = Pembayaran::where('id_booking_treatment', $booking->id_booking_treatment)->first();

            if ($pembayaran && strtolower($pembayaran->status_pembayaran) === 'berhasil') {
                return response()->json([
                    'message' => 'Booking treatment ini sudah dibayar.',
                    'data' => [
                        'id_pembayaran' => $pembayaran->id_pembayaran,
                        'status_pembayaran' => $pembayaran->status_pembayaran,
                    ],
                ], 400);
            }

            if (!$pembayaran) {
                $pembayaran = Pembayaran::create([
                    'id_booking_treatment' => $booking->id_booking_treatment,
                    'id_penjualan_produk'  => null,
                    'status_pembayaran'    => 'Pending',
                    'metode_pembayaran'    => 'Non Tunai',
                    'waktu_pembayaran'     => null,
                    'gross_amount'         => $grossAmount,
                ]);
            } else {
                $pembayaran->update([
                    'status_pembayaran' => 'Pending',
                    'metode_pembayaran' => 'Non Tunai',
                    'gross_amount'      => $grossAmount,
                ]);
            }

            // Siapkan payload Midtrans (menyerupai createProductPayment)
            $orderId = 'TRT-' . $booking->id_booking_treatment . '-' . now()->format('YmdHis');

            $payload = [
                'transaction_details' => [
                    'order_id'     => $orderId,
                    'gross_amount' => (int) round($grossAmount),
                ],
                'item_details' => [
                    [
                        'id'       => 'TRT-' . $booking->id_booking_treatment,
                        'price'    => (int) round($grossAmount),
                        'quantity' => 1,
                        'name'     => 'Pembayaran Treatment #' . $booking->id_booking_treatment,
                    ],
                ],
                'customer_details' => [
                    'first_name' => $user->nama_user ?? 'Customer',
                    'email'      => $user->email ?? 'no-reply@example.com',
                    'phone'      => $user->no_telp ?? '',
                ],
                'expiry' => [
                    'start_time' => now()->format('Y-m-d H:i:s T'),
                    'unit'       => 'hours',
                    'duration'   => 24,
                ],
            ];

            // Jika project Anda sudah pakai MidtransService di createProductPayment, boleh ganti baris ini
            // menjadi: $transaction = $this->midtransService->createTransaction($payload);
            $transaction = Snap::createTransaction($payload);

            // Update pembayaran dengan token dan URL
            $pembayaran->update([
                'snap_token'        => $transaction->token ?? null,
                'snap_url'          => $transaction->redirect_url ?? null,
                'order_id'          => $orderId,
                'status_pembayaran' => 'Pending',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Snap URL pembayaran treatment berhasil dibuat.',
                'data' => [
                    'id_pembayaran'  => $pembayaran->id_pembayaran,
                    'order_id'       => $orderId,
                    'snap_token'     => $transaction->token ?? null,
                    'snap_url'       => $transaction->redirect_url ?? null,
                    'gross_amount'   => (int) round($grossAmount),
                    'status'         => 'Pending',
                    'id_booking_treatment' => $booking->id_booking_treatment,
                ],
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('createTreatmentPayment error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'req'   => $request->all(),
            ]);

            return response()->json([
                'message' => 'Gagal membuat Snap URL pembayaran treatment.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PERBAIKI: Membuat Snap URL pembayaran untuk produk - GUNAKAN EXISTING PAYMENT
     */
    public function createProductPayment(Request $request)
    {
        // TAMBAHAN: Debug konfigurasi sebelum memproses
        Log::info('Midtrans config check', [
            'server_key_set' => !empty(config('midtrans.server_key')),
            'server_key_length' => strlen(config('midtrans.server_key') ?? ''),
            'client_key_set' => !empty(config('midtrans.client_key')),
            'client_key_length' => strlen(config('midtrans.client_key') ?? ''),
            'is_production' => config('midtrans.is_production'),
            'Config_serverKey_set' => !empty(Config::$serverKey),
            'Config_clientKey_set' => !empty(Config::$clientKey),
        ]);

        // Validasi konfigurasi - TAMBAH CLIENT KEY
        if (empty(config('midtrans.server_key')) || empty(config('midtrans.client_key'))) {
            return response()->json([
                'message' => 'Konfigurasi Midtrans belum lengkap',
                'error' => 'Server Key atau Client Key tidak ditemukan',
                'debug' => [
                    'server_key_set' => !empty(config('midtrans.server_key')),
                    'client_key_set' => !empty(config('midtrans.client_key')),
                    'env_check' => [
                        'MIDTRANS_SERVER_KEY' => !empty(env('MIDTRANS_SERVER_KEY')),
                        'MIDTRANS_CLIENT_KEY' => !empty(env('MIDTRANS_CLIENT_KEY')),
                    ],
                    'Config_check' => [
                        'Config_serverKey_set' => !empty(Config::$serverKey),
                        'Config_clientKey_set' => !empty(Config::$clientKey),
                    ]
                ]
            ], 500);
        }

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

            // PERBAIKI: Gunakan pembayaran yang sudah ada (dibuat saat store penjualan)
            $pembayaran = Pembayaran::where('id_penjualan_produk', $penjualan->id_penjualan_produk)->first();

            if (!$pembayaran) {
                // Jika tidak ada, buat baru (backup)
                $pembayaran = Pembayaran::create([
                    'id_booking_treatment' => null,
                    'id_penjualan_produk' => $penjualan->id_penjualan_produk,
                    'status_pembayaran' => 'Pending',
                    'metode_pembayaran' => 'Non Tunai',
                    'waktu_pembayaran' => null,
                    'gross_amount' => $penjualan->harga_akhir,
                ]);
                Log::info('Pembayaran baru dibuat', ['id_pembayaran' => $pembayaran->id_pembayaran]);
            } else {
                // Cek status pembayaran existing
                if ($pembayaran->status_pembayaran == 'Berhasil') {
                    return response()->json([
                        'message' => 'Produk ini sudah dibayar',
                    ], 400);
                }

                // Update gross amount jika belum ada
                if (!$pembayaran->gross_amount) {
                    $pembayaran->update(['gross_amount' => $penjualan->harga_akhir]);
                }

                Log::info('Menggunakan pembayaran existing', [
                    'id_pembayaran' => $pembayaran->id_pembayaran,
                    'status' => $pembayaran->status_pembayaran
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
                'order_id' => $snapData['order_id'],
                'status_pembayaran' => 'Pending', // Pastikan status Pending
                'metode_pembayaran' => 'Non Tunai'
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
     * PERBAIKI: Handle Midtrans notification webhook
     */
    public function handleNotification(Request $request)
    {
        try {
            Log::info('Midtrans notification received', [
                'body' => $request->all(),
                'headers' => $request->headers->all()
            ]);

            $notification = new Notification();

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

            $pembayaran = Pembayaran::where('order_id', $orderId)->first();

            if (!$pembayaran) {
                Log::error('Pembayaran not found', [
                    'order_id' => $orderId,
                    'available_orders' => Pembayaran::whereNotNull('order_id')->pluck('order_id')->toArray()
                ]);
                return response()->json(['message' => 'Payment not found'], 404);
            }

            DB::beginTransaction();

            $updateData = [
                'transaction_id' => $transactionId,
                'transaction_status' => $transactionStatus,
                'payment_type' => $paymentType,
                'midtrans_response' => json_encode($request->all()),
                'gross_amount' => $grossAmount,
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

            // PERBAIKI: Mapping status berdasarkan dokumentasi Midtrans yang sebenarnya
            switch ($transactionStatus) {
                case 'capture':
                    // Untuk credit card, cek fraud status
                    if ($fraudStatus == 'accept') {
                        $updateData['status_pembayaran'] = Pembayaran::STATUS_BERHASIL;
                        $updateData['waktu_pembayaran'] = now();
                        $updateData['metode_pembayaran'] = 'Non Tunai';
                    } elseif ($fraudStatus == 'challenge') {
                        $updateData['status_pembayaran'] = Pembayaran::STATUS_PENDING;
                    } else {
                        $updateData['status_pembayaran'] = Pembayaran::STATUS_GAGAL;
                    }
                    break;

                case 'settlement':
                    // INI YANG BENAR: Settlement = Berhasil
                    $updateData['status_pembayaran'] = Pembayaran::STATUS_BERHASIL;
                    $updateData['waktu_pembayaran'] = now();
                    $updateData['metode_pembayaran'] = 'Non Tunai';
                    break;

                case 'pending':
                    // Transaksi pending (menunggu pembayaran)
                    $updateData['status_pembayaran'] = Pembayaran::STATUS_PENDING;
                    break;

                case 'deny':
                    // Transaksi ditolak
                    $updateData['status_pembayaran'] = Pembayaran::STATUS_GAGAL;
                    break;

                case 'expire':
                    // Transaksi kedaluwarsa
                    $updateData['status_pembayaran'] = Pembayaran::STATUS_EXPIRED;
                    break;

                case 'cancel':
                    // Transaksi dibatalkan
                    $updateData['status_pembayaran'] = Pembayaran::STATUS_DIBATALKAN;
                    break;

                case 'refund':
                case 'partial_refund':
                    // Transaksi di-refund
                    $updateData['status_pembayaran'] = Pembayaran::STATUS_REFUND;
                    break;

                case 'failure':
                    // Transaksi gagal
                    $updateData['status_pembayaran'] = Pembayaran::STATUS_GAGAL;
                    break;

                default:
                    // Status tidak dikenali, tetap pending
                    $updateData['status_pembayaran'] = Pembayaran::STATUS_PENDING;
                    Log::warning('Unknown transaction status', [
                        'transaction_status' => $transactionStatus,
                        'order_id' => $orderId
                    ]);
                    break;
            }

            Log::info('Updating payment', [
                'id_pembayaran' => $pembayaran->id_pembayaran,
                'old_status' => $pembayaran->status_pembayaran,
                'new_status' => $updateData['status_pembayaran'],
                'transaction_status' => $transactionStatus,
                'update_data' => $updateData
            ]);

            $pembayaran->update($updateData);

            // Jika pembayaran berhasil, update status terkait
            if ($pembayaran->isSuccess()) {
                $this->handleSuccessfulPayment($pembayaran);
            }

            DB::commit();

            return response()->json([
                'message' => 'Notifikasi pembayaran berhasil diproses',
                'data' => [
                    'order_id' => $orderId,
                    'status_pembayaran' => $updateData['status_pembayaran'],
                    'transaction_status' => $transactionStatus,
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
     * UPDATE: Enhanced handleSuccessfulPayment method
     */
    private function handleSuccessfulPayment(Pembayaran $pembayaran)
    {
        try {
            DB::beginTransaction();

            // Jika pembayaran untuk produk
            if ($pembayaran->id_penjualan_produk) {
                $penjualan = PembelianProduk::find($pembayaran->id_penjualan_produk);
                if ($penjualan && $penjualan->status_pengambilan_produk === 'Belum diambil') {
                    $penjualan->update([
                        'status_pengambilan_produk' => 'Siap diambil'
                    ]);

                    Log::info('Product status updated to ready for pickup', [
                        'id_penjualan' => $penjualan->id_penjualan_produk,
                        'old_status' => 'Belum diambil',
                        'new_status' => 'Siap diambil'
                    ]);
                }
            }

            // Jika pembayaran untuk treatment
            if ($pembayaran->id_booking_treatment) {
                $booking = BookingTreatment::find($pembayaran->id_booking_treatment);
                if ($booking && in_array($booking->status_booking, ['Pending', 'Menunggu Pembayaran'])) {
                    $booking->update([
                        'status_booking' => 'Dikonfirmasi'
                    ]);

                    Log::info('Booking treatment status updated to confirmed', [
                        'id_booking' => $booking->id_booking_treatment,
                        'old_status' => $booking->getOriginal('status_booking'),
                        'new_status' => 'Dikonfirmasi'
                    ]);
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error handling successful payment', [
                'id_pembayaran' => $pembayaran->id_pembayaran,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * BARU: Check pembayaran dengan auto-sync dari Midtrans
     * GET /api/midtrans/check/{id_pembayaran}
     */
    public function checkPaymentWithSync($id)
    {
        try {
            Log::info('Requesting payment detail with auto-sync', ['id_pembayaran' => $id]);

            // Load pembayaran dengan relasi
            $pembayaran = Pembayaran::with(['penjualanProduk.user', 'penjualanProduk.detailPembelian.produk', 'bookingTreatment.user', 'bookingTreatment.detailBooking'])
                ->find($id);

            if (!$pembayaran) {
                return response()->json([
                    'message' => 'Data Pembayaran tidak ditemukan'
                ], 404);
            }

            $syncInfo = null;

            // AUTO-SYNC: Cek status terbaru dari Midtrans jika ada order_id dan belum final
            if ($pembayaran->order_id && !$pembayaran->isFinalStatus()) {
                Log::info('Auto-syncing payment status from Midtrans', [
                    'order_id' => $pembayaran->order_id,
                    'current_status' => $pembayaran->status_pembayaran,
                    'current_transaction_status' => $pembayaran->transaction_status
                ]);

                $syncResult = $this->syncPaymentStatusFromMidtrans($pembayaran);

                if ($syncResult['updated']) {
                    // Reload pembayaran setelah update dengan fresh relations
                    $pembayaran = $pembayaran->fresh(['penjualanProduk.user', 'penjualanProduk.detailPembelian.produk', 'bookingTreatment.user', 'bookingTreatment.detailBooking']);

                    Log::info('Payment status auto-synced successfully', [
                        'order_id' => $pembayaran->order_id,
                        'old_status' => $syncResult['old_status'],
                        'new_status' => $pembayaran->status_pembayaran,
                        'transaction_status' => $pembayaran->transaction_status
                    ]);

                    $syncInfo = [
                        'synced' => true,
                        'old_status' => $syncResult['old_status'],
                        'new_status' => $pembayaran->status_pembayaran,
                        'old_transaction_status' => $syncResult['old_transaction_status'] ?? null,
                        'new_transaction_status' => $pembayaran->transaction_status,
                        'last_sync' => now(),
                        'sync_source' => 'midtrans_transaction_api'
                    ];
                } else {
                    $syncInfo = [
                        'synced' => false,
                        'error' => $syncResult['error'] ?? 'Unknown sync error',
                        'current_status' => $pembayaran->status_pembayaran,
                        'last_sync_attempt' => now(),
                        'sync_source' => 'midtrans_transaction_api'
                    ];
                }
            } else {
                $syncInfo = [
                    'synced' => false,
                    'reason' => $pembayaran->order_id ?
                        'Status pembayaran sudah final (tidak perlu sync)' :
                        'Tidak ada order_id untuk sync',
                    'current_status' => $pembayaran->status_pembayaran,
                    'is_final_status' => $pembayaran->isFinalStatus(),
                    'has_order_id' => !empty($pembayaran->order_id)
                ];
            }

            // Determine response message based on payment type
            $message = 'Data Pembayaran ditemukan';
            if ($pembayaran->id_penjualan_produk) {
                $message = 'Data Pembayaran Produk ditemukan';
            } elseif ($pembayaran->id_booking_treatment) {
                $message = 'Data Pembayaran Treatment ditemukan';
            }

            return response()->json([
                'message' => $message,
                'data' => $pembayaran,
                'sync_info' => $syncInfo,
                'payment_info' => [
                    'type' => $pembayaran->id_penjualan_produk ? 'product' : 'treatment',
                    'order_id' => $pembayaran->order_id,
                    'transaction_id' => $pembayaran->transaction_id,
                    'status_pembayaran' => $pembayaran->status_pembayaran,
                    'transaction_status' => $pembayaran->transaction_status,
                    'payment_type' => $pembayaran->payment_type,
                    'gross_amount' => $pembayaran->gross_amount,
                    'va_number' => $pembayaran->va_number,
                    'bank' => $pembayaran->bank,
                    'waktu_pembayaran' => $pembayaran->waktu_pembayaran,
                    'is_success' => $pembayaran->isSuccess(),
                    'is_pending' => $pembayaran->isPending(),
                    'is_failed' => $pembayaran->isFailed()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in checkPaymentWithSync: ' . $e->getMessage(), [
                'id_pembayaran' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Gagal mengambil data pembayaran dengan sync',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * BARU: Sync status pembayaran dari Midtrans Transaction API
     */
    private function syncPaymentStatusFromMidtrans(Pembayaran $pembayaran)
    {
        try {
            Log::info('Starting sync from Midtrans', [
                'order_id' => $pembayaran->order_id,
                'current_status' => $pembayaran->status_pembayaran
            ]);

            // Cek status langsung dari Midtrans Transaction API
            $midtransStatus = Transaction::status($pembayaran->order_id);

            Log::info('Midtrans status response received', [
                'order_id' => $pembayaran->order_id,
                'transaction_status' => $midtransStatus->transaction_status ?? 'unknown',
                'payment_type' => $midtransStatus->payment_type ?? 'unknown',
                'gross_amount' => $midtransStatus->gross_amount ?? 'unknown'
            ]);

            $oldStatus = $pembayaran->status_pembayaran;
            $oldTransactionStatus = $pembayaran->transaction_status;

            $updateData = [
                'transaction_id' => $midtransStatus->transaction_id ?? null,
                'transaction_status' => $midtransStatus->transaction_status ?? null,
                'payment_type' => $midtransStatus->payment_type ?? null,
                'midtrans_response' => json_encode($midtransStatus),
                'gross_amount' => $midtransStatus->gross_amount ?? $pembayaran->gross_amount,
            ];

            // Extract VA number dan bank info jika ada
            $this->extractVirtualAccountInfo($midtransStatus, $updateData);

            // Mapping status dari Midtrans ke status internal
            $transactionStatus = $midtransStatus->transaction_status ?? 'pending';
            $fraudStatus = $midtransStatus->fraud_status ?? null;

            $this->mapMidtransStatusToInternal($transactionStatus, $fraudStatus, $updateData);

            Log::info('Updating payment with new data', [
                'order_id' => $pembayaran->order_id,
                'old_status' => $oldStatus,
                'new_status' => $updateData['status_pembayaran'],
                'old_transaction_status' => $oldTransactionStatus,
                'new_transaction_status' => $updateData['transaction_status']
            ]);

            // Update pembayaran
            $pembayaran->update($updateData);

            // Jika status berubah ke berhasil, update status terkait
            if ($pembayaran->isSuccess() && $oldStatus !== Pembayaran::STATUS_BERHASIL) {
                $this->handleSuccessfulPayment($pembayaran);
            }

            return [
                'updated' => true,
                'old_status' => $oldStatus,
                'new_status' => $updateData['status_pembayaran'],
                'old_transaction_status' => $oldTransactionStatus,
                'new_transaction_status' => $updateData['transaction_status'],
                'transaction_id' => $updateData['transaction_id'],
                'payment_type' => $updateData['payment_type'],
                'midtrans_data' => $midtransStatus
            ];

        } catch (\Exception $e) {
            Log::error('Error syncing payment status from Midtrans', [
                'order_id' => $pembayaran->order_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'updated' => false,
                'error' => $e->getMessage(),
                'old_status' => $pembayaran->status_pembayaran,
                'new_status' => $pembayaran->status_pembayaran
            ];
        }
    }

    /**
     * HELPER: Extract Virtual Account information
     */
    private function extractVirtualAccountInfo($midtransStatus, &$updateData)
    {
        if (isset($midtransStatus->va_numbers) && is_array($midtransStatus->va_numbers) && count($midtransStatus->va_numbers) > 0) {
            $updateData['va_number'] = $midtransStatus->va_numbers[0]->va_number ?? null;
            $updateData['bank'] = $midtransStatus->va_numbers[0]->bank ?? null;
        } elseif (isset($midtransStatus->permata_va_number)) {
            $updateData['va_number'] = $midtransStatus->permata_va_number;
            $updateData['bank'] = 'permata';
        } elseif (isset($midtransStatus->bca_va_number)) {
            $updateData['va_number'] = $midtransStatus->bca_va_number;
            $updateData['bank'] = 'bca';
        } elseif (isset($midtransStatus->bill_key)) {
            $updateData['va_number'] = $midtransStatus->bill_key;
            $updateData['bank'] = 'mandiri';
        }
    }

    /**
     * HELPER: Map Midtrans status to internal status
     */
    private function mapMidtransStatusToInternal($transactionStatus, $fraudStatus, &$updateData)
    {
        switch ($transactionStatus) {
            case 'capture':
                if ($fraudStatus == 'accept') {
                    $updateData['status_pembayaran'] = Pembayaran::STATUS_BERHASIL;
                    $updateData['waktu_pembayaran'] = now();
                    $updateData['metode_pembayaran'] = 'Non Tunai';
                } elseif ($fraudStatus == 'challenge') {
                    $updateData['status_pembayaran'] = Pembayaran::STATUS_PENDING;
                } else {
                    $updateData['status_pembayaran'] = Pembayaran::STATUS_GAGAL;
                }
                break;

            case 'settlement':
                $updateData['status_pembayaran'] = Pembayaran::STATUS_BERHASIL;
                $updateData['waktu_pembayaran'] = now();
                $updateData['metode_pembayaran'] = 'Non Tunai';
                break;

            case 'pending':
                $updateData['status_pembayaran'] = Pembayaran::STATUS_PENDING;
                break;

            case 'deny':
                $updateData['status_pembayaran'] = Pembayaran::STATUS_GAGAL;
                break;

            case 'expire':
                $updateData['status_pembayaran'] = Pembayaran::STATUS_EXPIRED;
                break;

            case 'cancel':
                $updateData['status_pembayaran'] = Pembayaran::STATUS_DIBATALKAN;
                break;

            case 'refund':
            case 'partial_refund':
                $updateData['status_pembayaran'] = Pembayaran::STATUS_REFUND;
                break;

            case 'failure':
                $updateData['status_pembayaran'] = Pembayaran::STATUS_GAGAL;
                break;

            default:
                $updateData['status_pembayaran'] = Pembayaran::STATUS_PENDING;
                Log::warning('Unknown transaction status', [
                    'transaction_status' => $transactionStatus
                ]);
                break;
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
    /**
     * TAMBAHAN: Debug endpoint untuk cek mapping status
     */
    public function debugStatusMapping()
    {
        return response()->json([
            'message' => 'Status Mapping Reference',
            'midtrans_to_our_status' => [
                'settlement' => 'Berhasil (Settlement adalah status pembayaran berhasil)',
                'capture' => 'Berhasil (jika fraud_status = accept)',
                'pending' => 'Pending (menunggu pembayaran customer)',
                'deny' => 'Gagal (ditolak oleh bank/payment gateway)',
                'expire' => 'Expired (waktu pembayaran habis)',
                'cancel' => 'Dibatalkan (dibatalkan oleh customer/sistem)',
                'refund' => 'Refund (dikembalikan)',
                'partial_refund' => 'Refund (dikembalikan sebagian)',
                'failure' => 'Gagal (transaksi gagal)'
            ],
            'our_status_enum' => [
                'Belum Dibayar' => 'Default saat pembayaran dibuat',
                'Pending' => 'Menunggu pembayaran dari customer',
                'Berhasil' => 'Pembayaran berhasil (settlement/capture)',
                'Gagal' => 'Pembayaran gagal/ditolak',
                'Sudah Dibayar' => 'Untuk pembayaran tunai',
                'Menunggu Pembayaran' => 'Menunggu konfirmasi manual',
                'Dibatalkan' => 'Pembayaran dibatalkan',
                'Expired' => 'Waktu pembayaran habis',
                'Refund' => 'Pembayaran dikembalikan'
            ],
            'example_from_midtrans' => [
                'PRD-4-1752553871' => 'Settlement ✅',
                'PRD-1-1752511375' => 'Settlement ✅',
                'PRD-1-1752510727' => 'Settlement ✅',
                'PRD-1-1752510152' => 'Settlement ✅',
                'PRD-2-1752508042' => 'Settlement ✅',
                'PRD-1-1752498978' => 'Expired ❌'
            ]
        ]);
    }

    public function getApiInfo()
    {
        return response()->json([
            'status' => 'OK',
            'message' => 'Simple test endpoint working',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
