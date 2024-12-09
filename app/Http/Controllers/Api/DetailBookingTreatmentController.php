<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailBookingTreatment;
use Illuminate\Http\Request;

class DetailBookingTreatmentController extends Controller
{
    public function index()
    {
        return DetailBookingTreatment::with(['treatment', 'dokter', 'beautician', 'booking'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_booking_treatment' => 'required|exists:tb_booking_treatment,id_booking_treatment',
            'id_treatment' => 'required|exists:tb_treatment,id_treatment',
            'harga_akhir_treatment' => 'required|numeric',
            'potongan_harga' => 'numeric|nullable',
            'id_dokter' => 'exists:tb_dokter,id_dokter|nullable',
            'id_beautician' => 'exists:tb_beautician,id_beautician|nullable',
        ]);

        $detailBooking = DetailBookingTreatment::create($validated);

        return response()->json($detailBooking, 201);
    }

    public function show($id)
    {
        $detailBooking = DetailBookingTreatment::with(['treatment', 'dokter', 'beautician', 'booking'])->find($id);

        if (!$detailBooking) {
            return response()->json(['message' => 'Detail Booking Treatment not found'], 404);
        }

        return response()->json($detailBooking);
    }

    public function update(Request $request, $id)
    {
        $detailBooking = DetailBookingTreatment::find($id);

        if (!$detailBooking) {
            return response()->json(['message' => 'Detail Booking Treatment not found'], 404);
        }

        $validated = $request->validate([
            'id_booking_treatment' => 'exists:tb_booking_treatment,id_booking_treatment',
            'id_treatment' => 'exists:tb_treatment,id_treatment',
            'harga_akhir_treatment' => 'numeric',
            'potongan_harga' => 'numeric|nullable',
            'id_dokter' => 'exists:tb_dokter,id_dokter|nullable',
            'id_beautician' => 'exists:tb_beautician,id_beautician|nullable',
        ]);

        $detailBooking->update($validated);

        return response()->json($detailBooking);
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
