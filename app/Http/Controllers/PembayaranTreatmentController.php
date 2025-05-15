<?php

namespace App\Http\Controllers;

use App\Models\PembayaranTreatment;
use App\Models\BookingTreatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranTreatmentController extends Controller
{
    // Menampilkan semua pembayaran treatment
    public function index()
    {
        $pembayaranTreatmentList = PembayaranTreatment::with('bookingTreatment')->get();
        return response()->json($pembayaranTreatmentList);
    }

    public function show($id)
    {
        // Mencari data pembayaran treatment berdasarkan ID
        $pembayaranTreatment = PembayaranTreatment::with('bookingTreatment.user', 'bookingTreatment.detailBooking')->find($id);

        // Jika data tidak ditemukan, kembalikan respons error
        if (!$pembayaranTreatment) {
            return response()->json([
                'message' => 'Pembayaran Treatment tidak ditemukan',
            ], 404);
        }

        // Mengembalikan data pembayaran treatment
        return response()->json([
            'message' => 'Data Pembayaran Treatment ditemukan',
            'data' => $pembayaranTreatment
        ]);
    }

    // Menyimpan pembayaran treatment baru
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validasi input pembayaran treatment
            $validatedPayment = $request->validate([
                'id_booking_treatment' => 'required|exists:tb_booking_treatment,id_booking_treatment',
                'metode_pembayaran' => 'required|string',
                'pajak' => 'required|numeric|min:0', // Validasi pajak
            ]);

            // Ambil data booking treatment berdasarkan id_booking_treatment
            $bookingTreatment = BookingTreatment::findOrFail($validatedPayment['id_booking_treatment']);
            $hargaAkhirTreatment = $bookingTreatment->harga_akhir_treatment;

            // Ambil pajak yang dimasukkan
            $pajakPersen = $validatedPayment['pajak'];

            // Hitung pajak berdasarkan persentase yang dimasukkan
            $pajakAmount = $hargaAkhirTreatment * ($pajakPersen / 100);

            // Hitung total dengan menambahkan pajak ke harga_akhir_treatment
            $total = $hargaAkhirTreatment + $pajakAmount;

            // Menyimpan pembayaran treatment
            $pembayaranTreatment = PembayaranTreatment::create([
                'id_booking_treatment' => $validatedPayment['id_booking_treatment'],
                'harga_akhir_treatment' => $hargaAkhirTreatment,
                'metode_pembayaran' => $validatedPayment['metode_pembayaran'],
                'pajak' => $pajakPersen,
                'total' => $total,
            ]);

            DB::commit();

            return response()->json([
                'pembayaran_treatment' => $pembayaranTreatment,
                'message' => 'Pembayaran treatment berhasil disimpan'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error while creating pembayaran treatment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Memperbarui pembayaran treatment yang sudah ada
    public function update(Request $request, $id)
    {
        $request->validate([
            'uang' => 'required|numeric|min:0' // Validasi uang yang dibayar
        ]);

        DB::beginTransaction();

        try {
            // Ambil data pembayaran treatment
            $pembayaranTreatment = PembayaranTreatment::findOrFail($id);

            // Ambil data booking treatment terkait
            $bookingTreatment = $pembayaranTreatment->bookingTreatment;

            // Hitung kembalian
            $uang = $request->uang; // Uang yang dibayar
            $total = $pembayaranTreatment->total; // Total bayar (sudah termasuk pajak)
            $kembalian = $uang - $total;

            // Update data pembayaran
            $pembayaranTreatment->uang = $uang;
            $pembayaranTreatment->kembalian = $kembalian;
            $pembayaranTreatment->save();

            // Memperbarui status pembayaran pada booking treatment menjadi "Sudah Dibayar"
            $bookingTreatment->status_pembayaran = 'Sudah Dibayar';
            $bookingTreatment->save();

            DB::commit();

            return response()->json([
                'pembayaran_treatment' => $pembayaranTreatment,
                'message' => 'Pembayaran treatment berhasil diperbarui'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error while updating pembayaran treatment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
