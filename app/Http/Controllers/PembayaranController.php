<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\BookingTreatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Models\PembelianProduk;

class PembayaranController extends Controller
{
    // ——— PEMBAYARAN TREATMENT ———

    /** GET  /api/pembayaran-treatment */
    public function indexTreatment()
    {
        // kopi paste dari PembayaranTreatmentController@index
        $list = Pembayaran::with('bookingTreatment.user')
            ->whereNotNull('id_booking_treatment')
            ->get();

        return response()->json($list);
    }

    /** GET  /api/pembayaran-treatment/{id} */
    public function showTreatment($id)
    {
        // kopi paste dari PembayaranTreatmentController@show
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

    /** PUT  /api/pembayaran-treatment/{id} */
    public function updateTreatment(Request $request, $id)
    {
        $request->validate([
            'metode_pembayaran' => 'required|string|in:Tunai,Non Tunai',
            'uang'               => 'nullable|numeric|min:0',
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
                $pembayaran->uang              = $request->uang;
                $pembayaran->kembalian         = $request->uang - $hargaAkhir;
                $pembayaran->status_pembayaran = 'Sudah Dibayar';
                $pembayaran->waktu_pembayaran  = now();
            } else {
                $pembayaran->uang              = null;
                $pembayaran->kembalian         = null;
                $pembayaran->status_pembayaran = 'Belum Dibayar';
                $pembayaran->waktu_pembayaran  = null;
            }

            $pembayaran->save();
            DB::commit();

            return response()->json([
                'pembayaran_produk' => $pembayaran,
                'message'           => 'Pembayaran treatment berhasil diperbarui',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error while updating pembayaran treatment',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /** GET  /api/pembayaran-treatment/total-bayar */
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

    // ——— PEMBAYARAN PRODUK ———

    /** GET  /api/pembayaran-produk */
    public function indexProduk()
    {
        // kopi paste dari PembayaranProdukController@index
        $list = Pembayaran::with('penjualanProduk.user')
            ->whereNotNull('id_penjualan_produk')
            ->get();

        return response()->json($list);
    }

    /** GET  /api/pembayaran-produk/{id} */
    public function showProduk($id)
    {
        // kopi paste dari PembayaranProdukController@show
        $pembayaran = Pembayaran::with('penjualanProduk.user', 'penjualanProduk.detailPembelian')->find($id);

        if (!$pembayaran) {
            return response()->json(['message' => 'Pembayaran Treatment tidak ditemukan'], 404);
        }
        if (is_null($pembayaran->id_penjualan_produk)) {
            return response()->json(['message' => 'Pembayaran ini bukan pembayaran produk'], 400);
        }

        return response()->json([
            'message' => 'Data Pembayaran Treatment ditemukan',
            'data' => $pembayaran
        ]);
    }

    public function storeProduk(Request $request)
    {
        $request->validate([
            'id_penjualan_produk' => 'required|exists:tb_penjualan_produk,id_penjualan_produk',
            'metode_pembayaran'   => 'required|string|in:Tunai,Non Tunai',
            'uang'                => 'nullable|numeric|min:0',
        ]);
    
        DB::beginTransaction();
        try {
            $penjualan = PembelianProduk::findOrFail($request->id_penjualan_produk);
            $hargaAkhir = $penjualan->harga_akhir;
    
            // Default null
            $uang = null;
            $kembalian = null;
            $statusPembayaran = 'Belum Dibayar';
            $waktuPembayaran  = null;
    
            if ($request->metode_pembayaran === 'Tunai') {
                $uang = $request->uang;
                $kembalian = $request->uang - $hargaAkhir;
                $statusPembayaran = 'Sudah Dibayar';
                $waktuPembayaran  = now();
            }
    
            $pembayaran = Pembayaran::create([
                'id_booking_treatment'   => null,
                'id_penjualan_produk'    => $request->id_penjualan_produk,
                'uang'                   => $uang,
                'kembalian'              => $kembalian,
                'metode_pembayaran'      => $request->metode_pembayaran,
                'status_pembayaran'      => $statusPembayaran,
                'waktu_pembayaran'       => $waktuPembayaran,
            ]);
    
            DB::commit();
    
            return response()->json([
                'message'  => 'Pembayaran produk berhasil disimpan',
                'data'     => $pembayaran,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error while creating pembayaran produk',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    

    /** PUT  /api/pembayaran-produk/{id} */
    public function updateProduk(Request $request, $id)
    {
        // kopi paste dari PembayaranProdukController@update
        $request->validate([
            'metode_pembayaran' => 'required|string|in:Tunai,Non Tunai',
            'uang'               => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $pembayaran = Pembayaran::findOrFail($id);
            $hargaAkhir = $pembayaran->penjualanProduk->harga_akhir;

            $pembayaran->uang              = $request->uang;
            $pembayaran->kembalian         = $request->uang - $hargaAkhir;
            $pembayaran->metode_pembayaran = $request->metode_pembayaran;
            $pembayaran->status_pembayaran = 'Sudah Dibayar';
            $pembayaran->waktu_pembayaran  = now();
            $pembayaran->save();

            DB::commit();

            return response()->json([
                'pembayaran_produk' => $pembayaran,
                'message' => 'Pembayaran produk berhasil diperbarui',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error while updating pembayaran produk', 'error' => $e->getMessage()], 500);
        }
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
