<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailBookingTreatment;
use App\Models\BookingTreatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Promo;
use App\Models\Treatment;

class DetailBookingTreatmentController extends Controller
{
    public function index()
    {

        // Mengambil seluruh data booking treatment dengan relasi ke user dan promo
        $bookingTreatments = BookingTreatment::with(['user', 'promo'])->get();

        // Cek jika data ditemukan
        if ($bookingTreatments->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data booking treatment',
            ], 404);
        }

        // Mengembalikan data booking treatment dengan status 200 OK
        return response()->json([
            'booking_treatments' => $bookingTreatments,
            'message' => 'Data booking treatment berhasil diambil',
        ], 200);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validasi input booking
            $validatedBooking = $request->validate([
                'id_user' => 'required|exists:tb_user,id_user',
                'waktu_treatment' => 'required|date',
                'status_booking_treatment' => 'required|string',
                'id_promo' => 'nullable|exists:tb_promo,id_promo',  // Validasi id_promo
                'details' => 'required|array',
                'details.*.id_treatment' => 'required|exists:tb_treatment,id_treatment',
                'details.*.id_dokter' => 'nullable|exists:tb_dokter,id_dokter',
                'details.*.id_beautician' => 'nullable|exists:tb_beautician,id_beautician',
            ]);

            // Membuat Booking Treatment
            $booking = BookingTreatment::create([
                'id_user' => $validatedBooking['id_user'],
                'waktu_treatment' => $validatedBooking['waktu_treatment'],
                'status_booking_treatment' => $validatedBooking['status_booking_treatment'],
                'id_promo' => $validatedBooking['id_promo'],  // Menyimpan id_promo
                'harga_total' => 0,
                'harga_akhir_treatment' => 0,
                'potongan_harga' => 0,  // Awalnya potongan_harga di-set 0
            ]);

            $hargaTotal = 0;

            // Memasukkan detail booking treatment (lebih dari satu treatment)
            foreach ($validatedBooking['details'] as $detail) {
                $treatment = Treatment::find($detail['id_treatment']);

                if (!$treatment) {
                    throw new \Exception("Treatment ID {$detail['id_treatment']} not found");
                }

                $biayaTreatment = $treatment->biaya_treatment;

                $detail['id_booking_treatment'] = $booking->id_booking_treatment;
                $detail['biaya_treatment'] = $biayaTreatment;

                DetailBookingTreatment::create($detail);

                $hargaTotal += $biayaTreatment;
            }

            // Mengambil promo berdasarkan id_promo
            $promo = Promo::find($validatedBooking['id_promo']);
            $potonganHarga = 0;

            if ($promo) {
                // Jika promo ditemukan, ambil potongan harga dari promo
                $potonganHarga = $promo->potongan_harga;
            }

            // Hitung harga akhir treatment dengan potongan harga dari promo
            $hargaAkhir = $hargaTotal - $potonganHarga;

            // Update harga total, potongan harga, dan harga akhir treatment pada BookingTreatment
            $booking->update([
                'harga_total' => $hargaTotal,
                'potongan_harga' => $potonganHarga,  // Menyimpan potongan harga
                'harga_akhir_treatment' => $hargaAkhir > 0 ? $hargaAkhir : 0,
            ]);

            DB::commit();

            return response()->json([
                'booking_treatment' => $booking,
                'message' => 'Booking and details saved successfully',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error while creating data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        // Mencari data booking treatment berdasarkan ID
        $bookingTreatment = BookingTreatment::with(['user', 'promo', 'detailBooking'])
            ->find($id);

        // Cek jika data booking treatment tidak ditemukan
        if (!$bookingTreatment) {
            return response()->json([
                'message' => 'Booking treatment tidak ditemukan',
            ], 404);
        }

        // Mengembalikan data booking treatment beserta detail booking treatment
        return response()->json([
            'booking_treatment' => $bookingTreatment,
            'message' => 'Detail booking treatment berhasil diambil',
        ], 200);
    }


    public function update(Request $request, $id)
{
    // Cari detail booking treatment berdasarkan ID
    $detailBooking = DetailBookingTreatment::find($id);

    if (!$detailBooking) {
        return response()->json(['message' => 'Detail Booking Treatment not found'], 404);
    }

    // Validasi input untuk data dokter dan beautician
    $validated = $request->validate([
        'id_dokter' => 'exists:tb_dokter,id_dokter|nullable',
        'id_beautician' => 'exists:tb_beautician,id_beautician|nullable',
    ]);

    // Update data detail booking treatment
    $detailBooking->update($validated);

    return response()->json([
        'detail_booking' => $detailBooking,
        'message' => 'Detail Booking Treatment updated successfully',
    ]);
}

public function updateStatusBooking(Request $request, $id)
{
    // Validasi input untuk status booking treatment
    $validated = $request->validate([
        'status_booking_treatment' => 'string|nullable',
    ]);

    // Cari booking treatment berdasarkan ID
    $bookingTreatment = BookingTreatment::find($id);

    if (!$bookingTreatment) {
        return response()->json(['message' => 'Booking Treatment not found'], 404);
    }

    // Update status booking treatment
    $bookingTreatment->update($validated);

    return response()->json([
        'booking_treatment' => $bookingTreatment,
        'message' => 'Status Booking Treatment updated successfully',
    ]);
}

    public function destroy($id)
    {
        $detailBooking = DetailBookingTreatment::find($id);

        if (!$detailBooking) {
            return response()->json(['message' => 'Detail Booking Treatment not found'], 404);
        }

        $detailBooking->delete();

        return response()->json(['message' => 'Detail Booking Treatment deleted successfully']);
    }
}
