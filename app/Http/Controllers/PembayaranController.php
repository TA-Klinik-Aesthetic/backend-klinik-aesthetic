<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\BookingTreatment;
use App\Models\Produk;
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
            return response()->json(['message' => 'Pembayaran treatment tidak ditemukan'], 404);
        }
        if (is_null($pembayaran->id_booking_treatment)) {
            return response()->json(['message' => 'Pembayaran ini bukan pembayaran treatment'], 400);
        }

        return response()->json([
            'message' => 'Data pembayaran treatment ditemukan',
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

        $statuses = ['Sudah Dibayar', 'Berhasil'];

        $total = Pembayaran::whereNotNull('id_booking_treatment')
            ->whereIn('status_pembayaran', $statuses)
            ->whereYear('waktu_pembayaran', $year)
            ->count();

        $perbulan = Pembayaran::whereNotNull('id_booking_treatment')
            ->whereIn('status_pembayaran', $statuses)
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
            return response()->json(['message' => 'Pembayaran produk tidak ditemukan'], 404);
        }
        if (is_null($pembayaran->id_penjualan_produk)) {
            return response()->json(['message' => 'Pembayaran ini bukan pembayaran produk'], 400);
        }

        return response()->json([
            'message' => 'Data pembayaran produk ditemukan',
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

                // ─── Tambahkan: kurangi stok produk ─────
                // Ambil semua detail produk yang dibeli
                $penjualan->detailPembelian->each(function ($detail) {
                    $produk = Produk::findOrFail($detail->id_produk);
                    // Pastikan stok cukup (bisa juga di-handle di front/backend saat storeKasir)

                    $stokBaru = $produk->stok_produk - $detail->jumlah_produk;
                    if ($stokBaru < 0) {
                        throw new \Exception("Stok produk {$produk->nama_produk} tidak mencukupi saat pembayaran.");
                    }

                    // update stok & status_produk
                    $produk->update([
                        'stok_produk'   => $stokBaru,
                        'status_produk' => $stokBaru > 0 ? 'Tersedia' : 'Habis',
                    ]);
                });
                // ──────────────────────────────────────────
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

    public function confirmPayment(Request $request, $id)
    {
        $request->validate([
            'gambar_bukti_pembayaran' => 'required|image', // max 2 MB
        ]);

        DB::beginTransaction();
        try {
            // 1. Cari record pembayaran
            $pembayaran = Pembayaran::findOrFail($id);

            // hanya Non Tunai yang boleh lewat sini
            if ($pembayaran->metode_pembayaran !== 'Non Tunai') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya pembayaran Non Tunai yang dapat dikonfirmasi di endpoint ini.'
                ], 422);
            }

            // 2. Cek dulu: hanya yang belum dibayar saja
            if ($pembayaran->status_pembayaran === 'Sudah Dibayar') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran sudah dikonfirmasi sebelumnya.'
                ], 422);
            }

            if ($request->hasFile('gambar_bukti_pembayaran')) {
                $file     = $request->file('gambar_bukti_pembayaran');
                $fileName = time() . '_' . $file->getClientOriginalName();
                // simpan ke public/gambar_bukti_pembayaran
                $file->move(public_path('gambar_bukti_pembayaran'), $fileName);
                // simpan path ke kolom yang sesuai
                $pembayaran->gambar_bukti_pembayaran = 'gambar_bukti_pembayaran/' . $fileName;
            }

            // 3. Ambil penjualan & harga akhir
            $penjualan  = $pembayaran->penjualanProduk;
            $hargaAkhir = $penjualan->harga_akhir;

            // 4. Kurangi stok untuk tiap produk di detail penjualan
            foreach ($penjualan->detailPembelian as $detail) {
                $produk = Produk::findOrFail($detail->id_produk);

                $stokBaru = $produk->stok_produk - $detail->jumlah_produk;
                if ($stokBaru < 0) {
                    throw new \Exception("Stok produk {$produk->nama_produk} tidak mencukupi.");
                }

                $produk->update([
                    'stok_produk'   => $stokBaru,
                    'status_produk' => $stokBaru > 0 ? 'Tersedia' : 'Habis',
                ]);
            }

            // 5. Tandai sudah dibayar, set waktu, uang & kembalian
            //    Untuk Non Tunai tetap kita anggap sudah dibayar penuh tanpa kembalian
            $pembayaran->status_pembayaran = 'Sudah Dibayar';
            $pembayaran->waktu_pembayaran   = now();
            $pembayaran->uang               = $hargaAkhir;
            $pembayaran->kembalian          = 0;
            $pembayaran->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dikonfirmasi.',
                'data'    => $pembayaran
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function confirmPaymentTreatment(Request $request, $id)
    {
        $request->validate([
            'gambar_bukti_pembayaran' => 'required|image',
        ]);

        DB::beginTransaction();

        try {
            // 1. Ambil record pembayaran
            $pembayaran = Pembayaran::findOrFail($id);

            // 2. Hanya Non Tunai boleh lewat sini
            if ($pembayaran->metode_pembayaran !== 'Non Tunai') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya pembayaran Non Tunai yang dapat dikonfirmasi di endpoint ini.'
                ], 422);
            }

            // 3. Pastikan belum dibayar
            if ($pembayaran->status_pembayaran === 'Sudah Dibayar') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran sudah dikonfirmasi sebelumnya.'
                ], 422);
            }

            if ($request->hasFile('gambar_bukti_pembayaran')) {
                $file     = $request->file('gambar_bukti_pembayaran');
                $fileName = time() . '_' . $file->getClientOriginalName();
                // simpan ke public/gambar_bukti_pembayaran
                $file->move(public_path('gambar_bukti_pembayaran'), $fileName);
                // simpan path ke kolom yang sesuai
                $pembayaran->gambar_bukti_pembayaran = 'gambar_bukti_pembayaran/' . $fileName;
            }

            // 4. Ambil booking treatment & harga akhir treatment
            $booking   = $pembayaran->bookingTreatment;
            $hargaAkhir = $booking->harga_akhir_treatment;

            // 5. Tandai sudah dibayar penuh tanpa kembalian
            $pembayaran->status_pembayaran = 'Sudah Dibayar';
            $pembayaran->waktu_pembayaran  = now();
            $pembayaran->uang              = $hargaAkhir;
            $pembayaran->kembalian         = 0;
            $pembayaran->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran treatment berhasil dikonfirmasi.',
                'data'    => $pembayaran
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    /** PUT  /api/pembayaran-produk/{id} */
    public function updateProduk(Request $request, $id)
    {
        $request->validate([
            'uang' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $pembayaran  = Pembayaran::findOrFail($id);
            $penjualan   = $pembayaran->penjualanProduk;
            $hargaAkhir  = $penjualan->harga_akhir;

            // 1) Hitung kembalian & tandai sudah dibayar
            $pembayaran->uang              = $request->uang;
            $pembayaran->kembalian         = $request->uang - $hargaAkhir;
            $pembayaran->status_pembayaran = 'Sudah Dibayar';
            $pembayaran->waktu_pembayaran  = now();
            $pembayaran->save();

            // 2) Kurangi stok sekaligus update status_produk
            foreach ($penjualan->detailPembelian as $item) {
                $produk   = Produk::findOrFail($item->id_produk);
                $newStock = $produk->stok_produk - $item->jumlah_produk;

                if ($newStock < 0) {
                    throw new \Exception("Stok produk {$produk->nama_produk} tidak mencukupi.");
                }

                $produk->update([
                    'stok_produk'   => $newStock,
                    'status_produk' => $newStock > 0 ? 'Tersedia' : 'Habis',
                ]);
            }

            DB::commit();

            return response()->json([
                'pembayaran_produk' => $pembayaran,
                'message'           => 'Pembayaran produk berhasil diperbarui',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error while updating pembayaran produk',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    /** GET  /api/pembayaran-produk/total-bayar */
    public function totalBayarProduk(Request $request)
    {
        $year = $request->query('year', date('Y'));

        $statuses = ['Sudah Dibayar', 'Berhasil'];

        $total = Pembayaran::whereNotNull('id_penjualan_produk')
            ->whereIn('status_pembayaran', $statuses)
            ->whereYear('waktu_pembayaran', $year)
            ->count();

        $perbulan = Pembayaran::whereNotNull('id_penjualan_produk')
            ->whereIn('status_pembayaran', $statuses)
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

    public function updateMetodePembayaranProduk(Request $request, $id)
    {
        $request->validate([
            'metode_pembayaran' => 'required|string|in:Tunai,Non Tunai',
        ]);

        $pembayaran = Pembayaran::findOrFail($id);
        $pembayaran->metode_pembayaran = $request->metode_pembayaran;
        $pembayaran->save();

        return response()->json([
            'success' => true,
            'message' => 'Metode pembayaran produk berhasil diperbarui.',
            'data'    => $pembayaran,
        ]);
    }
}
