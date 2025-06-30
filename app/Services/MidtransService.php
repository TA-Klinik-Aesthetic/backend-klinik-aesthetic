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

    /**
     * Membuat token pembayaran untuk treatment dengan metode pembayaran spesifik
     *
     * @param BookingTreatment $booking
     * @param Pembayaran $pembayaran
     * @param string $paymentMethod
     * @return array|null
     */
    public function createTransactionTokenTreatment(BookingTreatment $booking, Pembayaran $pembayaran, $paymentMethod = null)
    {
        try {
            $user = $booking->user;

            if (!$user) {
                Log::error('User tidak ditemukan untuk BookingTreatment ID: ' . $booking->id_booking_treatment);
                return null;
            }

            $item_details = [
                [
                    'id' => 'treatment-' . $booking->id_booking_treatment,
                    'price' => intval($booking->harga_akhir_treatment),
                    'quantity' => 1,
                    'name' => 'Treatment Booking #' . $booking->id_booking_treatment,
                ]
            ];

            // Add detail items if available
            if ($booking->detailBooking && !$booking->detailBooking->isEmpty()) {
                foreach ($booking->detailBooking as $detail) {
                    if ($detail->treatmentDetail) {
                        $item_details[] = [
                            'id' => 'detail-' . $detail->id_detail_booking,
                            'price' => 0,
                            'quantity' => 1,
                            'name' => $detail->treatmentDetail->nama_detail_treatment ?? 'Detail Treatment',
                        ];
                    }
                }
            }

            $transaction_details = [
                'order_id' => 'TRT-' . $pembayaran->id_pembayaran . '-' . time(),
                'gross_amount' => intval($booking->harga_akhir_treatment),
            ];

            $customer_details = [
                'first_name' => $user->nama_lengkap ?? 'Customer',
                'email' => $user->email ?? 'customer@example.com',
                'phone' => $user->nomor_telepon ?? '08123456789',
            ];

            // Konfigurasikan metode pembayaran yang spesifik
            $enabled_payments = $this->getEnabledPaymentMethods($paymentMethod);

            // Format data transaksi
            $transaction_data = [
                'transaction_details' => $transaction_details,
                'item_details' => $item_details,
                'customer_details' => $customer_details,
                'credit_card' => [
                    'secure' => true
                ],
            ];

            // Tambahkan enabled_payments jika ada
            if (!empty($enabled_payments)) {
                $transaction_data['enabled_payments'] = $enabled_payments;
            }

            // Tambahkan konfigurasi spesifik berdasarkan metode pembayaran
            $this->addPaymentSpecificConfig($transaction_data, $paymentMethod);

            // Log transaction data untuk debugging
            Log::info('Mengirim data treatment ke Midtrans', [
                'transaction_data' => $transaction_data,
                'payment_method' => $paymentMethod
            ]);

            $transactionToken = Snap::getSnapToken($transaction_data);

            $pembayaran->update([
                'order_id' => $transaction_details['order_id'],
                'snap_token' => $transactionToken,
            ]);

            Log::info('Token treatment berhasil dibuat', [
                'token' => $transactionToken
            ]);

            return [
                'token' => $transactionToken,
                'client_key' => config('midtrans.client_key'),
                'order_id' => $transaction_details['order_id'],
                'gross_amount' => $transaction_details['gross_amount'],
            ];
        } catch (\Exception $e) {
            Log::error('Error di createTransactionTokenTreatment: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Membuat token pembayaran untuk produk dengan metode pembayaran spesifik
     *
     * @param PembelianProduk $penjualan
     * @param Pembayaran $pembayaran
     * @param string $paymentMethod
     * @return array|null
     */
    public function createTransactionTokenProduk(PembelianProduk $penjualan, Pembayaran $pembayaran, $paymentMethod = null)
    {
        try {
            // Log untuk debugging
            Log::info('Memulai pembuatan token untuk produk', [
                'id_penjualan' => $penjualan->id_penjualan_produk,
                'id_pembayaran' => $pembayaran->id_pembayaran,
                'payment_method' => $paymentMethod
            ]);

            $user = $penjualan->user;
            if (!$user) {
                Log::error('User tidak ditemukan untuk PembelianProduk ID: ' . $penjualan->id_penjualan_produk);
                return null;
            }

            $item_details = [];

            // Cek apakah detailPembelian ada dan tidak kosong
            if (!$penjualan->detailPembelian || $penjualan->detailPembelian->isEmpty()) {
                $item_details[] = [
                    'id' => 'produk-' . $penjualan->id_penjualan_produk,
                    'price' => intval($penjualan->harga_akhir),
                    'quantity' => 1,
                    'name' => 'Pembelian Produk #' . $penjualan->id_penjualan_produk,
                ];
            } else {
                foreach ($penjualan->detailPembelian as $detail) {
                    if (!$detail->produk) continue;

                    $item_details[] = [
                        'id' => 'produk-' . $detail->id_detail_penjualan_produk,
                        'price' => intval($detail->harga_penjualan_produk),
                        'quantity' => $detail->jumlah_produk,
                        'name' => $detail->produk->nama_produk ?? 'Produk',
                    ];
                }
            }

            if (empty($item_details)) {
                $item_details[] = [
                    'id' => 'produk-' . $penjualan->id_penjualan_produk,
                    'price' => intval($penjualan->harga_akhir),
                    'quantity' => 1,
                    'name' => 'Pembelian Produk #' . $penjualan->id_penjualan_produk,
                ];
            }

            $transaction_details = [
                'order_id' => 'PRD-' . $pembayaran->id_pembayaran . '-' . time(),
                'gross_amount' => intval($penjualan->harga_akhir),
            ];

            $customer_details = [
                'first_name' => $user->nama_user ?? 'Customer',
                'email' => $user->email ?? 'customer@example.com',
                'phone' => $user->no_telp ?? '08123456789',
            ];

            // Data transaksi
            $transaction_data = [
                'transaction_details' => $transaction_details,
                'item_details' => $item_details,
                'customer_details' => $customer_details,
            ];

            // Tambahkan pengaturan metode pembayaran jika ditentukan
            if ($paymentMethod) {
                switch ($paymentMethod) {
                    case 'bca':
                    case 'bni':
                    case 'bri':
                        $transaction_data['enabled_payments'] = [$paymentMethod . '_va'];
                        break;
                    case 'mandiri':
                        $transaction_data['enabled_payments'] = ['echannel'];
                        break;
                    case 'gopay':
                    case 'shopeepay':
                    case 'qris':
                        $transaction_data['enabled_payments'] = [$paymentMethod];
                        break;
                }
            }

            // Log transaction data untuk debugging
            Log::info('Data transaksi Midtrans', $transaction_data);

            // Cek konfigurasi Midtrans
            $serverKey = config('midtrans.server_key');
            $clientKey = config('midtrans.client_key');
            $isProduction = config('midtrans.is_production');

            Log::info('Konfigurasi Midtrans', [
                'server_key_exists' => !empty($serverKey),
                'client_key_exists' => !empty($clientKey),
                'is_production' => $isProduction,
            ]);

            // Ambil token dari Midtrans
            try {
                $snapToken = \Midtrans\Snap::getSnapToken($transaction_data);

                Log::info('Token berhasil dibuat', ['token' => $snapToken]);

                // Update pembayaran dengan token dan order_id
                $pembayaran->update([
                    'order_id' => $transaction_details['order_id'],
                    'snap_token' => $snapToken,
                ]);

                return [
                    'token' => $snapToken,
                    'client_key' => $clientKey,
                    'order_id' => $transaction_details['order_id'],
                    'gross_amount' => $transaction_details['gross_amount'],
                ];
            } catch (\Exception $snapException) {
                Log::error('Error saat membuat Snap token: ' . $snapException->getMessage(), [
                    'exception' => $snapException,
                    'trace' => $snapException->getTraceAsString()
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Error di createTransactionTokenProduk: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Mendapatkan daftar metode pembayaran yang diaktifkan berdasarkan pilihan
     *
     * @param string $paymentMethod
     * @return array
     */
    private function getEnabledPaymentMethods($paymentMethod = null)
    {
        // Jika tidak ada metode pembayaran yang dipilih, kembalikan semua yang didukung
        if (!$paymentMethod) {
            return ['bca_va', 'bni_va', 'bri_va', 'echannel', 'gopay', 'shopeepay', 'qris'];
        }

        // Jika ada metode pembayaran yang spesifik
        switch ($paymentMethod) {
            case 'bca':
                return ['bca_va'];
            case 'bni':
                return ['bni_va'];
            case 'bri':
                return ['bri_va'];
            case 'mandiri':
                return ['echannel'];
            case 'gopay':
                return ['gopay'];
            case 'shopeepay':
                return ['shopeepay'];
            case 'qris':
                return ['qris'];
            default:
                return [];
        }
    }

    /**
     * Menambahkan konfigurasi spesifik untuk metode pembayaran tertentu
     *
     * @param array $transaction_data
     * @param string $paymentMethod
     */
    private function addPaymentSpecificConfig(&$transaction_data, $paymentMethod)
    {
        if (!$paymentMethod) {
            return;
        }

        switch ($paymentMethod) {
            case 'bca':
                $transaction_data['bca_va'] = [
                    'va_number' => rand(100000000000, 999999999999),
                    'free_text' => [
                        'inquiry' => [
                            [
                                'id' => 'text-id',
                                'en' => 'text-en'
                            ]
                        ],
                        'payment' => [
                            [
                                'id' => 'text-id',
                                'en' => 'text-en'
                            ]
                        ]
                    ]
                ];
                break;

            case 'bni':
                $transaction_data['bni_va'] = [
                    'va_number' => rand(100000000000, 999999999999),
                ];
                break;

            case 'bri':
                $transaction_data['bri_va'] = [
                    'va_number' => rand(100000000000, 999999999999),
                ];
                break;

            case 'mandiri':
                $transaction_data['echannel'] = [
                    'bill_info1' => 'Payment for:',
                    'bill_info2' => 'Klinik Aesthetic'
                ];
                break;

            case 'gopay':
                $transaction_data['gopay'] = [
                    'enable_callback' => true,
                    'callback_url' => url('/api/midtrans/gopay-callback')
                ];
                break;

            case 'shopeepay':
                $transaction_data['shopeepay'] = [
                    'callback_url' => url('/api/midtrans/shopeepay-callback')
                ];
                break;
        }
    }
}
