<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Pembayaran;
use App\Models\BookingTreatment;
use App\Models\PembelianProduk;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createSnapTokenTreatment(BookingTreatment $booking, Pembayaran $pembayaran)
    {
        $user = $booking->user;

        $item_details = [
            [
                'id' => 'treatment-' . $booking->id_booking_treatment,
                'price' => intval($booking->harga_akhir_treatment),
                'quantity' => 1,
                'name' => 'Treatment - ' . $booking->treatment->nama_treatment,
            ]
        ];

        foreach ($booking->detailBooking as $detail) {
            $item_details[] = [
                'id' => 'detail-' . $detail->id_detail_booking,
                'price' => 0,
                'quantity' => 1,
                'name' => $detail->treatmentDetail->nama_detail_treatment,
            ];
        }

        $transaction_details = [
            'order_id' => 'TRT-' . $pembayaran->id_pembayaran . '-' . time(),
            'gross_amount' => intval($booking->harga_akhir_treatment),
        ];

        $customer_details = [
            'first_name' => $user->nama_lengkap,
            'email' => $user->email,
            'phone' => $user->nomor_telepon,
        ];

        $enabled_payments = ['qris', 'gopay', 'shopeepay', 'bca_va', 'bni_va', 'bri_va', 'permata_va'];

        $transaction_data = [
            'transaction_details' => $transaction_details,
            'item_details' => $item_details,
            'customer_details' => $customer_details,
            'enabled_payments' => $enabled_payments,
        ];

        try {
            $snapToken = Snap::getSnapToken($transaction_data);
            $snapUrl = Snap::getSnapUrl($transaction_data);

            $pembayaran->update([
                'order_id' => $transaction_details['order_id'],
                'snap_token' => $snapToken,
                'snap_url' => $snapUrl,
            ]);

            return [
                'token' => $snapToken,
                'redirect_url' => $snapUrl,
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans error: ' . $e->getMessage());
            return null;
        }
    }

    public function createSnapTokenProduk(PembelianProduk $penjualan, Pembayaran $pembayaran)
    {
        $user = $penjualan->user;
        $item_details = [];

        foreach ($penjualan->detailPembelian as $detail) {
            $item_details[] = [
                'id' => 'produk-' . $detail->id_detail_pembelian,
                'price' => intval($detail->harga_jual),
                'quantity' => $detail->jumlah_produk,
                'name' => $detail->produk->nama_produk,
            ];
        }

        $transaction_details = [
            'order_id' => 'PRD-' . $pembayaran->id_pembayaran . '-' . time(),
            'gross_amount' => intval($penjualan->harga_akhir),
        ];

        $customer_details = [
            'first_name' => $user->nama_lengkap,
            'email' => $user->email,
            'phone' => $user->nomor_telepon,
        ];

        $enabled_payments = ['qris', 'gopay', 'shopeepay', 'bca_va', 'bni_va', 'bri_va', 'permata_va'];

        $transaction_data = [
            'transaction_details' => $transaction_details,
            'item_details' => $item_details,
            'customer_details' => $customer_details,
            'enabled_payments' => $enabled_payments,
        ];

        try {
            $snapToken = Snap::getSnapToken($transaction_data);
            $snapUrl = Snap::getSnapUrl($transaction_data);

            $pembayaran->update([
                'order_id' => $transaction_details['order_id'],
                'snap_token' => $snapToken,
                'snap_url' => $snapUrl,
            ]);

            return [
                'token' => $snapToken,
                'redirect_url' => $snapUrl,
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans error: ' . $e->getMessage());
            return null;
        }
    }
}
