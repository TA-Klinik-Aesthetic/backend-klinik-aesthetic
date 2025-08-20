<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Illuminate\Support\Facades\Log;
use App\Models\PembelianProduk;
use App\Models\Pembayaran;
use App\Models\BookingTreatment;
use App\Models\User;

class MidtransService
{
    public function __construct()
    {
        // Set konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = config('midtrans.is_sanitized', true);
        Config::$is3ds = config('midtrans.is_3ds', true);
    }

    /**
     * Membuat Snap URL untuk treatment
     */
    public function createSnapUrlTreatment($booking, $pembayaran)
    {
        try {
            $orderId = 'TRT-' . $pembayaran->id_pembayaran . '-' . time();

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $booking->harga_akhir_treatment,
                ],
                'customer_details' => [
                    'first_name' => $booking->user->nama_user,
                    'email' => $booking->user->email,
                    'phone' => $booking->user->no_telp,
                ],
                'item_details' => [
                    [
                        'id' => 'treatment-' . $booking->id_booking_treatment,
                        'price' => (int) $booking->harga_akhir_treatment,
                        'quantity' => 1,
                        'name' => $booking->treatment->nama_treatment ?? 'Treatment Booking',
                        'category' => 'Treatment'
                    ]
                ],
                'callbacks' => [
                    'finish' => config('app.url') . '/payment/finish',
                    'unfinish' => config('app.url') . '/payment/unfinish',
                    'error' => config('app.url') . '/payment/error'
                ]
            ];

            Log::info('Creating Snap URL for treatment', $params);

            $snapToken = Snap::getSnapToken($params);
            $snapUrl = Snap::getSnapUrl($params);

            return [
                'token' => $snapToken,
                'redirect_url' => $snapUrl,
                'order_id' => $orderId
            ];

        } catch (\Exception $e) {
            Log::error('Error creating Snap URL for treatment: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Membuat Snap URL untuk produk - MENGGUNAKAN HARGA AKHIR SEBAGAI GROSS AMOUNT
     */
    public function createSnapUrlProduk($penjualan, $pembayaran)
    {
        try {
            $orderId = 'PRD-' . $pembayaran->id_pembayaran . '-' . time();

            // Validasi data penjualan berdasarkan model yang ada
            if (!$penjualan->detailPembelian || $penjualan->detailPembelian->count() == 0) {
                throw new \Exception('Detail pembelian produk tidak ditemukan');
            }

            if (!$penjualan->harga_akhir || $penjualan->harga_akhir <= 0) {
                throw new \Exception('Harga akhir tidak valid: ' . $penjualan->harga_akhir);
            }

            // Gunakan harga_akhir sebagai total pembayaran
            $totalPayment = (int) $penjualan->harga_akhir;

            // Buat item details yang balance dengan total payment
            $itemDetails = [];

            // Method 1: Gunakan item individual dengan proporsi harga akhir
            $totalItemValue = 0;
            $tempItems = [];

            foreach ($penjualan->detailPembelian as $detail) {
                if (!$detail->jumlah_produk || $detail->jumlah_produk <= 0) {
                    throw new \Exception('Jumlah produk harus lebih dari 0');
                }

                if (!$detail->harga_penjualan_produk || $detail->harga_penjualan_produk <= 0) {
                    throw new \Exception('Harga penjualan produk tidak valid');
                }

                $itemSubtotal = $detail->harga_penjualan_produk * $detail->jumlah_produk;
                $totalItemValue += $itemSubtotal;

                $tempItems[] = [
                    'id' => 'product-' . $detail->id_produk,
                    'original_price' => $detail->harga_penjualan_produk,
                    'quantity' => (int) $detail->jumlah_produk,
                    'subtotal' => $itemSubtotal,
                    'name' => $detail->produk->nama_produk ?? 'Produk',
                    'category' => 'Product'
                ];
            }

            // Hitung proporsi untuk setiap item agar total = harga_akhir
            if ($totalItemValue > 0) {
                $lastIndex = count($tempItems) - 1;
                $runningTotal = 0;

                foreach ($tempItems as $index => $item) {
                    if ($index === $lastIndex) {
                        // Item terakhir: sisa dari total payment
                        $adjustedPrice = $totalPayment - $runningTotal;
                    } else {
                        // Proportional price based on harga_akhir
                        $proportion = $item['subtotal'] / $totalItemValue;
                        $adjustedPrice = (int) round($totalPayment * $proportion);
                        $runningTotal += $adjustedPrice;
                    }

                    $itemDetails[] = [
                        'id' => $item['id'],
                        'price' => $adjustedPrice,
                        'quantity' => 1, // Set quantity 1 dengan harga yang sudah disesuaikan
                        'name' => $item['name'],
                        'category' => $item['category']
                    ];
                }
            } else {
                // Fallback: single item dengan total harga akhir
                $itemDetails[] = [
                    'id' => 'product-bundle-' . $penjualan->id_penjualan_produk,
                    'price' => $totalPayment,
                    'quantity' => 1,
                    'name' => 'Pembelian Produk #' . $penjualan->id_penjualan_produk,
                    'category' => 'Product'
                ];
            }

            // Validasi final: pastikan total item details = gross amount
            $calculatedTotal = array_sum(array_map(function($item) {
                return $item['price'] * $item['quantity'];
            }, $itemDetails));

            if ($calculatedTotal != $totalPayment) {
                Log::warning('Item details total mismatch, using bundle item', [
                    'calculated' => $calculatedTotal,
                    'expected' => $totalPayment
                ]);

                // Fallback: gunakan single bundle item
                $itemDetails = [
                    [
                        'id' => 'product-bundle-' . $penjualan->id_penjualan_produk,
                        'price' => $totalPayment,
                        'quantity' => 1,
                        'name' => 'Pembelian Produk #' . $penjualan->id_penjualan_produk . ' (Total)',
                        'category' => 'Product'
                    ]
                ];
            }

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $totalPayment,
                ],
                'customer_details' => [
                    'first_name' => $penjualan->user->nama_user,
                    'email' => $penjualan->user->email,
                    'phone' => $penjualan->user->no_telp,
                ],
                'item_details' => $itemDetails,
                'callbacks' => [
                    'finish' => config('app.url') . '/payment/finish',
                    'unfinish' => config('app.url') . '/payment/unfinish',
                    'error' => config('app.url') . '/payment/error'
                ]
            ];

            Log::info('Creating Snap URL for product', [
                'penjualan_id' => $penjualan->id_penjualan_produk,
                'order_id' => $orderId,
                'gross_amount' => $totalPayment,
                'harga_total' => $penjualan->harga_total,
                'potongan_harga' => $penjualan->potongan_harga,
                'besaran_pajak' => $penjualan->besaran_pajak,
                'harga_akhir' => $penjualan->harga_akhir,
                'item_details' => $itemDetails
            ]);

            $snapToken = Snap::getSnapToken($params);
            $snapUrl = Snap::getSnapUrl($params);

            return [
                'token' => $snapToken,
                'redirect_url' => $snapUrl,
                'order_id' => $orderId
            ];

        } catch (\Exception $e) {
            Log::error('Error creating Snap URL for product: ' . $e->getMessage(), [
                'penjualan_id' => $penjualan->id_penjualan_produk ?? 'unknown',
                'harga_akhir' => $penjualan->harga_akhir ?? 'unknown',
                'detail_count' => $penjualan->detailPembelian ? $penjualan->detailPembelian->count() : 0
            ]);
            throw $e;
        }
    }

    public function getTransactionStatus($orderId)
    {
        try {
            $status = Transaction::status($orderId);

            Log::info('Midtrans transaction status', [
                'order_id' => $orderId,
                'status' => $status
            ]);

            return $status;

        } catch (\Exception $e) {
            Log::error('Error getting transaction status: ' . $e->getMessage(), [
                'order_id' => $orderId
            ]);
            throw $e;
        }
    }
}
