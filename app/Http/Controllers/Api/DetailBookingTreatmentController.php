<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailBookingTreatment;
use App\Models\BookingTreatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DetailBookingTreatmentController extends Controller
{
    public function index()
    {
        return DetailBookingTreatment::with(['treatment', 'dokter', 'beautician', 'booking'])->get();
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
    
        try {
            $validatedBooking = $request->validate([
                'id_user' => 'required|exists:tb_user,id_user',
                'waktu_treatment' => 'required|date',
                'status_booking_treatment' => 'required|string',
            ]);
    
            $booking = BookingTreatment::create($validatedBooking);
    
            $validatedDetails = $request->validate([
                'details' => 'required|array',
                'details.*.id_treatment' => 'required|exists:tb_treatment,id_treatment',
                'details.*.harga_akhir_treatment' => 'required|numeric',
                'details.*.potongan_harga' => 'string|nullable',
                'details.*.id_dokter' => 'exists:tb_dokter,id_dokter|nullable',
                'details.*.id_beautician' => 'exists:tb_beautician,id_beautician|nullable',
            ]);
    
            $details = array_map(function ($detail) use ($booking) {
                $detail['id_booking_treatment'] = $booking->id_booking_treatment;
                return $detail;
            }, $validatedDetails['details']);
    
            $detailBookings = DetailBookingTreatment::insert($details);
    
            DB::commit();
    
            return response()->json([
                'booking_treatment' => $booking,
                'detail_booking_treatments' => $details,
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
