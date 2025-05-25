<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BookingTreatment;
use App\Models\PembelianProduk;
use App\Models\PembayaranTreatment;
use App\Models\DetailBookingTreatment;
use App\Models\Treatment;
use App\Models\Dokter;
use App\Models\Beautician;
use Illuminate\Support\Facades\DB;



class LaporanController extends Controller
{
    public function indexTreatment()
    {
        // Ambil seluruh data booking treatment dengan detail booking, kompensasi, dan promo terkait
        $laporanPenjualan = BookingTreatment::with([
            'detailBooking.kompensasiDiberikan', // Mendapatkan kompensasi yang diberikan lewat detail booking treatment
            'detailBooking.treatment', // Mendapatkan informasi treatment untuk setiap detail booking
            'promo' // Mendapatkan informasi promo yang diterapkan
        ])
            ->where('status_pembayaran', 'Sudah Dibayar') // Hanya ambil yang sudah dibayar
            ->get();

        // Data laporan
        $laporanData = [];
        $promoUsageCount = [];

        foreach ($laporanPenjualan as $bookingTreatment) {

            // Cek jika promo digunakan, maka hitung promo hanya sekali per booking treatment
            $promoName = 'Tidak ada promo';

            if ($bookingTreatment->promo) {
                $promoName = $bookingTreatment->promo->nama_promo;

                // $bookingTotalPromo = $promoDiscount; // Set promo potongan harga hanya sekali per booking treatment
            }

            // Menambahkan data ke laporan untuk setiap detail booking treatment
            foreach ($bookingTreatment->detailBooking as $detail) {
                $laporanData[] = [
                    'waktu_treatment' => $bookingTreatment->waktu_treatment,
                    'nama_treatment' => $detail->treatment->nama_treatment, // Nama treatment per detail
                    'biaya_treatment' => $detail->biaya_treatment, // Biaya treatment per detail
                    'dokter' => $detail->dokter ? $detail->dokter->nama_dokter : 'Dokter Tidak Tersedia',
                    'beautician' => $detail->beautician ? $detail->beautician->nama_beautician : 'Beautician Tidak Tersedia',
                    'kompensasi' => $detail->kompensasiDiberikan ? $detail->kompensasiDiberikan->kompensasi->nama_kompensasi : 'Tidak ada kompensasi',
                ];
            }

            // Hitung promo usage count per booking treatment
            if ($promoName !== 'Tidak ada promo') {
                if (!isset($promoUsageCount[$promoName])) {
                    $promoUsageCount[$promoName] = [
                        'count' => 1, // Inisialisasi dengan 1 penggunaan
                        'potongan_harga' => $bookingTreatment->promo->potongan_harga, // Set potongan harga pertama
                    ];
                } else {
                    // Jika promo sudah ada, update count dan potongan harga
                    $promoUsageCount[$promoName]['count']++;
                }
            }


            // Tambahkan total potongan harga untuk booking treatment ini ke total promo keseluruhan
            // $totalPromo += $bookingTotalPromo;
        }

        // Mengembalikan hasil laporan dalam format JSON
        return response()->json([
            'success' => true,
            'data' => $laporanData,
            'promo_usage_count' => $promoUsageCount,
            // 'total_promo' => $totalPromo
        ]);
    }

    public function laporanHarianTreatment(Request $request)
    {
        // Ambil parameter tanggal dari query string
        $tanggal = $request->query('tanggal');

        // Ambil seluruh data booking treatment yang sesuai dengan tanggal yang diberikan
        $laporanPenjualan = BookingTreatment::with([
            'detailBooking.kompensasiDiberikan', // Mendapatkan kompensasi yang diberikan lewat detail booking treatment
            'detailBooking.treatment', // Mendapatkan informasi treatment untuk setiap detail booking
            'promo', // Mendapatkan informasi promo yang diterapkan
            'pembayaranTreatment' // Mengambil data pembayaran treatment yang sudah ada
        ])
            ->whereDate('waktu_treatment', $tanggal)  // Filter berdasarkan tanggal
            ->where('status_pembayaran', 'Sudah Dibayar') // Hanya ambil yang sudah dibayar
            ->get();

        // Data laporan
        $laporanData = [];
        $promoUsageCount = [];
        $penjualanTreatment = [];
        $totalUang = 0;
        $pajak = 10;  // Pajak akan dihitung disini
        $totalPendapatan = 0;

        foreach ($laporanPenjualan as $bookingTreatment) {
            // Menghitung total potongan harga untuk booking treatment ini
            $promoName = 'Tidak ada promo';

            if ($bookingTreatment->promo) {
                $promoName = $bookingTreatment->promo->nama_promo;
            }

            // Menghitung total penjualan treatment berdasarkan harga_akhir_treatment
            $totalUang += $bookingTreatment->harga_akhir_treatment;

            // Menambahkan data ke laporan untuk setiap detail booking treatment
            foreach ($bookingTreatment->detailBooking as $detail) {
                // Cek apakah treatment sudah ada dalam laporan penjualan
                $treatmentName = $detail->treatment->nama_treatment;
                if (isset($penjualanTreatment[$treatmentName])) {
                    $penjualanTreatment[$treatmentName]['count']++;
                    // Format total biaya agar konsisten
                    $penjualanTreatment[$treatmentName]['total_biaya'] += $detail->biaya_treatment;
                } else {
                    $penjualanTreatment[$treatmentName] = [
                        'count' => 1,
                        'total_biaya' => $detail->biaya_treatment
                    ];
                }

                $laporanData[] = [
                    'waktu_treatment' => $bookingTreatment->waktu_treatment,
                    'nama_treatment' => $detail->treatment->nama_treatment, // Nama treatment per detail
                    'biaya_treatment' => number_format($detail->biaya_treatment, 2, '.', ''), // Format biaya treatment
                    'dokter' => $detail->dokter ? $detail->dokter->nama_dokter : 'Dokter Tidak Tersedia',
                    'beautician' => $detail->beautician ? $detail->beautician->nama_beautician : 'Beautician Tidak Tersedia',
                    'kompensasi' => $detail->kompensasiDiberikan ? $detail->kompensasiDiberikan->kompensasi->nama_kompensasi : 'Tidak ada kompensasi',
                ];
            }

            // Format total_biaya untuk penjualan_treatment menjadi dua angka desimal
            foreach ($penjualanTreatment as $key => $value) {
                $penjualanTreatment[$key]['total_biaya'] = number_format($value['total_biaya'], 2, '.', '');
            }

            // Hitung promo usage count per booking treatment
            if ($promoName !== 'Tidak ada promo') {
                if (!isset($promoUsageCount[$promoName])) {
                    $promoUsageCount[$promoName] = [
                        'count' => 1, // Inisialisasi dengan 1 penggunaan
                        'potongan_harga' => $bookingTreatment->promo->potongan_harga, // Set potongan harga pertama
                    ];
                } else {
                    // Jika promo sudah ada, update count dan potongan harga
                    $promoUsageCount[$promoName]['count']++;
                }
            }

            // Menambahkan total pendapatan dari pembayaran treatment
            foreach ($bookingTreatment->pembayaranTreatment as $payment) {
                $totalPendapatan += $payment->total;
            }
        }

        // Format untuk total, pajak, dan total_pajak
        $totalUang = number_format($totalUang, 2, '.', '');
        $totalPendapatan = number_format($totalPendapatan, 2, '.', '');

        // Mengembalikan hasil laporan dalam format JSON
        return response()->json([
            'success' => true,
            'data' => $laporanData,
            'promo_usage_count' => $promoUsageCount,
            'penjualan_treatment' => $penjualanTreatment,
            'subtotal' => $totalUang,
            'pajak' => $pajak,
            'total_pendapatan' => $totalPendapatan,
        ]);
    }



    public function laporanBulananTreatment(Request $request)
    {
        // Ambil parameter bulan dan tahun dari query string
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        // Ambil seluruh data booking treatment yang sesuai dengan bulan dan tahun yang diberikan
        $laporanPenjualan = BookingTreatment::with([
            'detailBooking.kompensasiDiberikan',
            'detailBooking.treatment',
            'promo',
            'pembayaranTreatment'
        ])
            ->whereYear('waktu_treatment', $tahun)
            ->whereMonth('waktu_treatment', $bulan)
            ->where('status_pembayaran', 'Sudah Dibayar')
            ->get();

        // Data laporan
        $laporanData = [];
        $promoUsageCount = [];
        $penjualanTreatment = [];
        $totalUang = 0;
        $pajak = 10;  // Pajak akan dihitung disini
        $totalPendapatan = 0;

        foreach ($laporanPenjualan as $bookingTreatment) {
            $promoName = 'Tidak ada promo';
            if ($bookingTreatment->promo) {
                $promoName = $bookingTreatment->promo->nama_promo;
                $promoDiscount = $bookingTreatment->promo->potongan_harga;
            }

            // Menambahkan total uang yang dibayar berdasarkan harga_akhir_treatment
            $totalUang += $bookingTreatment->harga_akhir_treatment;

            // Menambahkan data ke laporan untuk setiap detail booking treatment
            foreach ($bookingTreatment->detailBooking as $detail) {
                // Cek apakah treatment sudah ada dalam laporan penjualan
                $treatmentName = $detail->treatment->nama_treatment;
                if (isset($penjualanTreatment[$treatmentName])) {
                    $penjualanTreatment[$treatmentName]['count']++;
                    // Format total_biaya menjadi 2 desimal
                    $penjualanTreatment[$treatmentName]['total_biaya'] += $detail->biaya_treatment;
                } else {
                    $penjualanTreatment[$treatmentName] = [
                        'count' => 1,
                        'total_biaya' => $detail->biaya_treatment
                    ];
                }

                $laporanData[] = [
                    'waktu_treatment' => $bookingTreatment->waktu_treatment,
                    'nama_treatment' => $detail->treatment->nama_treatment,
                    'biaya_treatment' => number_format($detail->biaya_treatment, 2, '.', ''),
                    'dokter' => $detail->dokter ? $detail->dokter->nama_dokter : 'Dokter Tidak Tersedia',
                    'beautician' => $detail->beautician ? $detail->beautician->nama_beautician : 'Beautician Tidak Tersedia',
                    'kompensasi' => $detail->kompensasiDiberikan ? $detail->kompensasiDiberikan->kompensasi->nama_kompensasi : 'Tidak ada kompensasi',
                ];
            }

            // Format total_biaya untuk penjualan_treatment menjadi dua angka desimal
            foreach ($penjualanTreatment as $key => $value) {
                $penjualanTreatment[$key]['total_biaya'] = number_format($value['total_biaya'], 2, '.', '');
            }


            // Hitung promo usage count per booking treatment
            if ($promoName !== 'Tidak ada promo') {
                if (!isset($promoUsageCount[$promoName])) {
                    $promoUsageCount[$promoName] = [
                        'count' => 1,
                        'potongan_harga' => $promoDiscount
                    ];
                } else {
                    $promoUsageCount[$promoName]['count']++;
                }
            }

            foreach ($bookingTreatment->pembayaranTreatment as $payment) {
                $totalPendapatan += $payment->total;
            }
        }

        // Formatkan total uang, total pendapatan, dan pajak
        $totalUang = number_format($totalUang, 2, '.', '');
        $totalPendapatan = number_format($totalPendapatan, 2, '.', '');

        return response()->json([
            'success' => true,
            'data' => $laporanData,
            'promo_usage_count' => $promoUsageCount,
            'penjualan_treatment' => $penjualanTreatment,
            'subtotal' => $totalUang,
            'pajak' => $pajak,
            'total_pendapatan' => $totalPendapatan,
        ]);
    }


    public function indexProduk()
    {
        // Ambil seluruh data pembelian produk dengan detail produk dan promo terkait
        $laporanPenjualan = PembelianProduk::with([
            'detailPembelian.produk',  // Mendapatkan informasi produk yang dibeli
            'promo', // Mendapatkan informasi promo yang diterapkan
            'pembayaranProduk' // Mengambil data pembayaran produk yang sudah ada
        ])
            ->where('status_pembayaran', 'Sudah Dibayar') // Hanya ambil yang sudah dibayar
            ->get();

        // Data laporan
        $laporanData = [];
        $promoUsageCount = [];
        $penjualanProduk = [];
        $totalProduk = 0;

        foreach ($laporanPenjualan as $penjualanProdukItem) {
            // Menambahkan data ke laporan untuk setiap detail pembelian produk
            foreach ($penjualanProdukItem->detailPembelian as $detail) {
                $laporanData[] = [
                    'tanggal_pembelian' => $penjualanProdukItem->tanggal_pembelian,
                    'nama_produk' => $detail->produk->nama_produk,
                    'jumlah_produk' => $detail->jumlah_produk,
                    'harga_penjualan_produk' => number_format($detail->harga_penjualan_produk, 2, '.', ''),
                ];
                // Menghitung total produk yang terjual
                $penjualanProduk[$detail->produk->nama_produk]['count'] = isset($penjualanProduk[$detail->produk->nama_produk]['count'])
                    ? $penjualanProduk[$detail->produk->nama_produk]['count'] + $detail->jumlah_produk
                    : $detail->jumlah_produk;

                $penjualanProduk[$detail->produk->nama_produk]['total_biaya'] = isset($penjualanProduk[$detail->produk->nama_produk]['total_biaya'])
                    ? $penjualanProduk[$detail->produk->nama_produk]['total_biaya'] + ($detail->harga_penjualan_produk * $detail->jumlah_produk)
                    : ($detail->harga_penjualan_produk * $detail->jumlah_produk);
            }

            // Hitung promo usage count per pembelian produk
            if ($penjualanProdukItem->promo) {
                $promoName = $penjualanProdukItem->promo->nama_promo;
                $promoDiscount = $penjualanProdukItem->promo->potongan_harga;
                if (!isset($promoUsageCount[$promoName])) {
                    $promoUsageCount[$promoName] = [
                        'count' => 1, // Inisialisasi dengan 1 penggunaan
                        'potongan_harga' => number_format($promoDiscount, 2, '.', '')
                    ];
                } else {
                    $promoUsageCount[$promoName]['count']++;
                }
            }
        }

        // Mengembalikan hasil laporan dalam format JSON
        return response()->json([
            'success' => true,
            'data' => $laporanData,
            'promo_usage_count' => $promoUsageCount,
            'penjualan_produk' => $penjualanProduk,
        ]);
    }

    public function laporanHarianProduk(Request $request)
    {
        // Ambil parameter tanggal dari query string
        $tanggal = $request->query('tanggal');

        // Ambil seluruh data pembelian produk yang sesuai dengan tanggal yang diberikan
        $laporanPenjualan = PembelianProduk::with([
            'detailPembelian.produk',  // Mendapatkan informasi produk yang dibeli
            'promo', // Mendapatkan informasi promo yang diterapkan
            'pembayaranProduk' // Mengambil data pembayaran produk yang sudah ada
        ])
            ->whereDate('tanggal_pembelian', $tanggal)  // Filter berdasarkan tanggal
            ->where('status_pembayaran', 'Sudah Dibayar')
            ->get();

        // Data laporan
        $laporanData = [];
        $promoUsageCount = [];
        $penjualanProduk = [];
        $totalPendapatan = 0;

        foreach ($laporanPenjualan as $penjualanProdukItem) {
            // Menambahkan data ke laporan untuk setiap detail pembelian produk
            foreach ($penjualanProdukItem->detailPembelian as $detail) {
                $laporanData[] = [
                    'tanggal_pembelian' => $penjualanProdukItem->tanggal_pembelian,
                    'nama_produk' => $detail->produk->nama_produk,
                    'jumlah_produk' => $detail->jumlah_produk,
                    'harga_penjualan_produk' => number_format($detail->harga_penjualan_produk, 2, '.', ''),
                ];
                // Menghitung total produk yang terjual
                $penjualanProduk[$detail->produk->nama_produk]['count'] = isset($penjualanProduk[$detail->produk->nama_produk]['count'])
                    ? $penjualanProduk[$detail->produk->nama_produk]['count'] + $detail->jumlah_produk
                    : $detail->jumlah_produk;

                $penjualanProduk[$detail->produk->nama_produk]['total_biaya'] = isset($penjualanProduk[$detail->produk->nama_produk]['total_biaya'])
                    ? $penjualanProduk[$detail->produk->nama_produk]['total_biaya'] + ($detail->harga_penjualan_produk * $detail->jumlah_produk)
                    : ($detail->harga_penjualan_produk * $detail->jumlah_produk);
            }

            // Hitung promo usage count per pembelian produk
            if ($penjualanProdukItem->promo) {
                $promoName = $penjualanProdukItem->promo->nama_promo;
                $promoDiscount = $penjualanProdukItem->promo->potongan_harga;
                if (!isset($promoUsageCount[$promoName])) {
                    $promoUsageCount[$promoName] = [
                        'count' => 1, // Inisialisasi dengan 1 penggunaan
                        'potongan_harga' => number_format($promoDiscount, 2, '.', '')
                    ];
                } else {
                    $promoUsageCount[$promoName]['count']++;
                }
            }
            // Menambahkan total pendapatan dari pembayaran produk
            foreach ($penjualanProdukItem->pembayaranProduk as $payment) {
                $totalPendapatan += $payment->harga_akhir;
            }
        }

        $totalPendapatan = number_format($totalPendapatan, 2, '.', '');

        // Mengembalikan hasil laporan dalam format JSON
        return response()->json([
            'success' => true,
            'data' => $laporanData,
            'promo_usage_count' => $promoUsageCount,
            'penjualan_produk' => $penjualanProduk,
            'total_pendapatan' => $totalPendapatan,
        ]);
    }

    public function laporanBulananProduk(Request $request)
    {
        // Ambil parameter bulan dan tahun dari query string
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        // Ambil seluruh data pembelian produk yang sesuai dengan bulan dan tahun yang diberikan
        $laporanPenjualan = PembelianProduk::with([
            'detailPembelian.produk', // Mendapatkan informasi produk untuk setiap detail pembelian
            'promo', // Mendapatkan informasi promo yang diterapkan
            'pembayaranProduk' // Mengambil data pembayaran produk yang sudah ada
        ])
            ->whereYear('tanggal_pembelian', $tahun)  // Filter berdasarkan tahun
            ->whereMonth('tanggal_pembelian', $bulan) // Filter berdasarkan bulan
            ->whereHas('pembayaranProduk', function ($query) {
                $query->where('status_pembayaran', 'Sudah Dibayar');  // Pastikan hanya yang sudah dibayar
            })
            ->get();

        // Data laporan
        $laporanData = [];
        $promoUsageCount = [];
        $penjualanProduk = [];
        $totalPendapatan = 0;

        foreach ($laporanPenjualan as $pembelianProduk) {
            // Cek jika promo digunakan, maka hitung promo hanya sekali per pembelian produk
            $promoName = 'Tidak ada promo';
            $promoDiscount = 0;

            if ($pembelianProduk->promo) {
                $promoName = $pembelianProduk->promo->nama_promo;
                $promoDiscount = $pembelianProduk->promo->potongan_harga;
            }

            // Menambahkan data ke laporan untuk setiap detail pembelian produk
            foreach ($pembelianProduk->detailPembelian as $detail) {
                $produkName = $detail->produk->nama_produk;
                if (isset($penjualanProduk[$produkName])) {
                    $penjualanProduk[$produkName]['count'] += $detail->jumlah_produk;
                    $penjualanProduk[$produkName]['total_biaya'] += $detail->harga_penjualan_produk * $detail->jumlah_produk;
                } else {
                    $penjualanProduk[$produkName] = [
                        'count' => $detail->jumlah_produk,
                        'total_biaya' => $detail->harga_penjualan_produk * $detail->jumlah_produk
                    ];
                }

                $laporanData[] = [
                    'tanggal_pembelian' => $pembelianProduk->tanggal_pembelian,
                    'nama_produk' => $detail->produk->nama_produk,
                    'jumlah_produk' => $detail->jumlah_produk,
                    'harga_penjualan_produk' => number_format($detail->harga_penjualan_produk, 2, '.', ''),
                ];
            }

            // Hitung promo usage count per pembelian produk
            if ($promoName !== 'Tidak ada promo') {
                if (!isset($promoUsageCount[$promoName])) {
                    $promoUsageCount[$promoName] = [
                        'count' => 1, // Inisialisasi dengan 1 penggunaan
                        'potongan_harga' => $promoDiscount // Set potongan harga pertama
                    ];
                } else {
                    $promoUsageCount[$promoName]['count']++;
                }
            }

            // Menambahkan total pendapatan dari pembayaran produk
            foreach ($pembelianProduk->pembayaranProduk as $payment) {
                $totalPendapatan += $payment->harga_akhir;
            }
        }

        $totalPendapatan = number_format($totalPendapatan, 2, '.', '');

        // Mengembalikan hasil laporan dalam format JSON
        return response()->json([
            'success' => true,
            'data' => $laporanData,
            'promo_usage_count' => $promoUsageCount,
            'penjualan_produk' => $penjualanProduk,
            'total_pendapatan' => $totalPendapatan,
        ]);
    }
}
