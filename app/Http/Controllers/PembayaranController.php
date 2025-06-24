<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\BookingTreatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Models\PembelianProduk;
use App\Services\MidtransService;

class PembayaranController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    // ——— PEMBAYARAN TREATMENT ———

    /** GET  /api/pembayaran-treatment */
    public function indexTreatment()
    {
        $list = Pembayaran::with('bookingTreatment.user')
            ->whereNotNull('id_booking_treatment')
            ->get();

        return response()->json($list);
    }

    /** GET  /api/pembayaran-treatment/{id} */
    public function showTreatment($id)
    {
        $pembayaran = Pembayaran::with('bookingTreatment.user', 'bookingTreatment.detailBooking')->find($id);

        if (!$pembayaran) {
            return response()->json(['message' => 'Pembayaran Treatment tidak ditemukan'], 404);
        }
        if (is_null($pembayaran->id_booking_treatment)) {
            return response()->json(['message' => 'Pembayaran ini bukan pembayaran treatment'], 400);
        }

        return response()->json([
            'message' => 'Data Pembayaran Treatment ditemukan',
            'data'    => $pembayaran
        ]);
    }

    /** POST /api/pembayaran-treatment/create */
    public function createTreatment(Request $request)
    {
        $request->validate([
            'id_booking_treatment' => 'required|exists:tb_booking_treatment,id_booking_treatment',
        ]);

        DB::beginTransaction();
        try {
            $booking = BookingTreatment::with('user', 'detailBooking.treatmentDetail')->findOrFail($request->id_booking_treatment);

            // Check if payment already exists
            $existingPayment = Pembayaran::where('id_booking_treatment', $booking->id_booking_treatment)->first();
            if ($existingPayment) {
                return response()->json([
                    'message' => 'Pembayaran untuk treatment ini sudah ada',
                    'data' => $existingPayment
                ], 400);
            }

            $pembayaran = Pembayaran::create([
                'id_booking_treatment' => $booking->id_booking_treatment,
                'id_penjualan_produk' => null,
                'status_pembayaran' => 'Pending',
            ]);

            // Create Midtrans Snap Token
            $snapData = $this->midtransService->createSnapTokenTreatment($booking, $pembayaran);

            if (!$snapData) {
                throw new \Exception('Failed to create payment token');
            }

            DB::commit();

            return response()->json([
                'message' => 'Pembayaran treatment berhasil dibuat',
                'data' => $pembayaran,
                'snap_token' => $snapData['token'],
                'redirect_url' => $snapData['redirect_url']
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error while creating payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /** PUT  /api/pembayaran-treatment/{id} */
    public function updateTreatment(Request $request, $id)
    {
        $request->validate([
            'metode_pembayaran' => 'required|string|in:Tunai,QRIS,Virtual Account,E-Wallet',
            'uang' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $pembayaran = Pembayaran::findOrFail($id);

            if (!$pembayaran->bookingTreatment) {
                return response()->json([
                    'message' => 'Data booking treatment tidak ditemukan pada pembayaran ini.',
                ], 400);
            }

            $hargaAkhir = $pembayaran->bookingTreatment->harga_akhir_treatment;

            $pembayaran->metode_pembayaran = $request->metode_pembayaran;

            if ($request->metode_pembayaran === 'Tunai') {
                $pembayaran->uang = $request->uang;
                $pembayaran->kembalian = $request->uang - $hargaAkhir;
                $pembayaran->status_pembayaran = 'Berhasil';
                $pembayaran->waktu_pembayaran = now();
            }

            $pembayaran->save();
            DB::commit();

            return response()->json([
                'pembayaran_treatment' => $pembayaran,
                'message' => 'Pembayaran treatment berhasil diperbarui',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error while updating pembayaran treatment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ——— PEMBAYARAN PRODUK ———

    /** GET  /api/pembayaran-produk */
    public function indexProduk()
    {
        $list = Pembayaran::with('penjualanProduk.user')
            ->whereNotNull('id_penjualan_produk')
            ->get();

        return response()->json($list);
    }

    /** GET  /api/pembayaran-produk/{id} */
    public function showProduk($id)
    {
        $pembayaran = Pembayaran::with('penjualanProduk.user', 'penjualanProduk.detailPembelian')->find($id);

        if (!$pembayaran) {
            return response()->json(['message' => 'Pembayaran Produk tidak ditemukan'], 404);
        }
        if (is_null($pembayaran->id_penjualan_produk)) {
            return response()->json(['message' => 'Pembayaran ini bukan pembayaran produk'], 400);
        }

        return response()->json([
            'message' => 'Data Pembayaran Produk ditemukan',
            'data' => $pembayaran
        ]);
    }

    /** POST /api/pembayaran-produk */
    public function storeProduk(Request $request)
    {
        $request->validate([
            'id_penjualan_produk' => 'required|exists:tb_penjualan_produk,id_penjualan_produk',
            'metode_pembayaran' => 'required|string|in:Tunai,QRIS,Virtual Account,E-Wallet',
            'uang' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $penjualan = PembelianProduk::with('user', 'detailPembelian.produk')
                ->findOrFail($request->id_penjualan_produk);
            $hargaAkhir = $penjualan->harga_akhir;

            // Default values
            $uang = null;
            $kembalian = null;
            $statusPembayaran = 'Pending';
            $waktuPembayaran = null;

            if ($request->metode_pembayaran === 'Tunai') {
                $uang = $request->uang;
                $kembalian = $request->uang - $hargaAkhir;
                $statusPembayaran = 'Berhasil';
                $waktuPembayaran = now();
            }

            $pembayaran = Pembayaran::create([
                'id_booking_treatment' => null,
                'id_penjualan_produk' => $request->id_penjualan_produk,
                'uang' => $uang,
                'kembalian' => $kembalian,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => $statusPembayaran,
                'waktu_pembayaran' => $waktuPembayaran,
            ]);

            // For non-cash payments, create Midtrans token
            if ($request->metode_pembayaran !== 'Tunai') {
                $snapData = $this->midtransService->createSnapTokenProduk($penjualan, $pembayaran);

                if (!$snapData) {
                    throw new \Exception('Failed to create payment token');
                }

                $responseData = [
                    'message' => 'Pembayaran produk berhasil disimpan',
                    'data' => $pembayaran,
                    'snap_token' => $snapData['token'],
                    'redirect_url' => $snapData['redirect_url']
                ];
            } else {
                $responseData = [
                    'message' => 'Pembayaran produk berhasil disimpan',
                    'data' => $pembayaran
                ];
            }

            DB::commit();
            return response()->json($responseData, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error while creating pembayaran produk',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /** POST /api/payment/notification */
    public function handleNotification(Request $request)
    {
        try {
            $notificationBody = $request->all();

            // Sample notification handling
            $orderId = $notificationBody['order_id'];
            $transactionStatus = $notificationBody['transaction_status'];
            $paymentType = $notificationBody['payment_type'];
            $transactionId = $notificationBody['transaction_id'];

            // Parse order ID to determine if it's a treatment or product payment
            $isProductPayment = str_contains($orderId, 'PRD-');

            // Get the payment record
            $idPembayaran = explode('-', $orderId)[1];
            $pembayaran = Pembayaran::find($idPembayaran);

            if (!$pembayaran) {
                return response()->json(['message' => 'Payment not found'], 404);
            }

            // Map payment type to readable format for our database
            $metodePembayaran = $this->mapPaymentType($paymentType);

            // Update payment information
            $pembayaran->payment_type = $paymentType;
            $pembayaran->transaction_id = $transactionId;
            $pembayaran->payment_details = $notificationBody;
            $pembayaran->metode_pembayaran = $metodePembayaran;

            // Update payment status based on Midtrans status
            if (in_array($transactionStatus, ['settlement', 'capture'])) {
                $pembayaran->status_pembayaran = 'Berhasil';
                $pembayaran->waktu_pembayaran = now();
            } elseif ($transactionStatus == 'pending') {
                $pembayaran->status_pembayaran = 'Pending';
            } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                $pembayaran->status_pembayaran = 'Gagal';
            }

            $pembayaran->save();

            return response()->json(['status' => 'OK']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error processing notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /** GET /api/payment/finish */
    public function finishPayment(Request $request)
    {
        try {
            $orderId = $request->query('order_id');
            $status = $request->query('transaction_status');

            // This will be the page seen by customers after payment
            // You can redirect them to a success/failure page in your Flutter app

            return response()->json([
                'status' => $status,
                'order_id' => $orderId,
                'message' => 'Payment completed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error finishing payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /** Helper method to map payment types */
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
            default:
                return 'Non Tunai';
        }
    }

    public function totalBayarTreatment(Request $request)
    {
        // kopi paste dari PembayaranTreatmentController@totalBayar
        $year = $request->query('year', date('Y'));

        $total = Pembayaran::whereNotNull('id_booking_treatment')
            ->where('status_pembayaran', 'Sudah Dibayar')
            ->whereYear('waktu_pembayaran', $year)
            ->count();

        $perbulan = Pembayaran::whereNotNull('id_booking_treatment')
            ->where('status_pembayaran', 'Sudah Dibayar')
            ->whereYear('waktu_pembayaran', $year)
            ->selectRaw("DATE_FORMAT(waktu_pembayaran, '%Y-%m') AS bulan, COUNT(*) AS total")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        return response()->json([
            'success'               => true,
            'total_treatment_bayar' => $total,
            'bayar_perbulan'        => $perbulan,
        ]);
    }

    /** GET  /api/pembayaran-produk/total-bayar */
    public function totalBayarProduk(Request $request)
    {
        $year = $request->query('year', date('Y'));

        $total = Pembayaran::whereNotNull('id_penjualan_produk')
            ->where('status_pembayaran', 'Sudah Dibayar')
            ->whereYear('waktu_pembayaran', $year)
            ->count();

        $perbulan = Pembayaran::whereNotNull('id_penjualan_produk')
            ->where('status_pembayaran', 'Sudah Dibayar')
            ->whereYear('waktu_pembayaran', $year)
            ->select(
                DB::raw("DATE_FORMAT(waktu_pembayaran, '%Y-%m') AS bulan"),
                DB::raw("COUNT(*) AS total")
            )
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        return response()->json([
            'success'            => true,
            'total_produk_bayar' => $total,
            'bayar_per_bulan'    => $perbulan,
        ]);
    }
}
