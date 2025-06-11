<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\BookingTreatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranTreatmentController extends Controller
{
    public function index()
    {
        // Ambil pembayaran (model Pembayaran) yang id_penjualan_produk-nya terisi
        $list = Pembayaran::with('bookingTreatment.user')
            ->whereNotNull('id_booking_treatment')
            ->get();

        return response()->json($list);
    }

    public function show($id)
    {
        // Mencari data pembayaran treatment berdasarkan ID
        $pembayaran = Pembayaran::with('bookingTreatment.user', 'bookingTreatment.detailBooking')->find($id);

        // Jika data tidak ditemukan, kembalikan respons error
        if (!$pembayaran) {
            return response()->json([
                'message' => 'Pembayaran Treatment tidak ditemukan',
            ], 404);
        }

        // Jika ini bukan pembayaran treatment
        if (is_null($pembayaran->id_booking_treatment)) {
            return response()->json([
                'message' => 'Pembayaran ini bukan pembayaran treatment',
            ], 400);
        }

        // Mengembalikan data pembayaran treatment
        return response()->json([
            'message' => 'Data Pembayaran Treatment ditemukan',
            'data' => $pembayaran
        ]);
    }

    // // Menyimpan pembayaran treatment baru
    // public function store(Request $request)
    // {
    //     DB::beginTransaction();

    //     try {
    //         // Validasi input pembayaran treatment
    //         $validatedPayment = $request->validate([
    //             'id_booking_treatment' => 'required|exists:tb_booking_treatment,id_booking_treatment',
    //             'metode_pembayaran' => 'required|string',
    //             'pajak' => 'required|numeric|min:0', // Validasi pajak
    //         ]);

    //         // Ambil data booking treatment berdasarkan id_booking_treatment
    //         $bookingTreatment = BookingTreatment::findOrFail($validatedPayment['id_booking_treatment']);
    //         $hargaAkhirTreatment = $bookingTreatment->harga_akhir_treatment;

    //         // Ambil pajak yang dimasukkan
    //         $pajakPersen = $validatedPayment['pajak'];

    //         // Hitung pajak berdasarkan persentase yang dimasukkan
    //         $pajakAmount = $hargaAkhirTreatment * ($pajakPersen / 100);

    //         // Hitung total dengan menambahkan pajak ke harga_akhir_treatment
    //         $total = $hargaAkhirTreatment + $pajakAmount;

    //         // Menyimpan pembayaran treatment
    //         $pembayaranTreatment = PembayaranTreatment::create([
    //             'id_booking_treatment' => $validatedPayment['id_booking_treatment'],
    //             'harga_akhir_treatment' => $hargaAkhirTreatment,
    //             'metode_pembayaran' => $validatedPayment['metode_pembayaran'],
    //             'pajak' => $pajakPersen,
    //             'total' => $total,
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'pembayaran_treatment' => $pembayaranTreatment,
    //             'message' => 'Pembayaran treatment berhasil disimpan'
    //         ], 201);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Error while creating pembayaran treatment',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // Memperbarui pembayaran treatment yang sudah ada
    public function update(Request $request, $id)
    {
        try {

            $request->validate([
                'metode_pembayaran' => 'required|string|in:Tunai,Non Tunai',
                'uang'               => 'required|numeric|min:0',
            ]);

            DB::beginTransaction();
            // ambil baris pembayaran
            $pembayaran = Pembayaran::findOrFail($id);

            // Hanya dari penjualan produk
            $hargaAkhir = $pembayaran->bookingTreatment->harga_akhir_treatment;

            // update pembayaran
            $pembayaran->uang              = $request->uang;
            $pembayaran->kembalian         = $request->uang - $hargaAkhir;
            $pembayaran->metode_pembayaran = $request->metode_pembayaran;
            $pembayaran->status_pembayaran = 'Sudah Dibayar';
            $pembayaran->waktu_pembayaran  = now();
            $pembayaran->save();

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
}
