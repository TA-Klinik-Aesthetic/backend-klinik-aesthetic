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
                'potongan_harga' => 'nullable|numeric|min:0',
                'details' => 'required|array',
                'details.*.id_treatment' => 'required|exists:tb_treatment,id_treatment',
                'details.*.id_dokter' => 'nullable|exists:tb_dokter,id_dokter',
                'details.*.id_beautician' => 'nullable|exists:tb_beautician,id_beautician',
            ]);
    
            $booking = BookingTreatment::create([
                'id_user' => $validatedBooking['id_user'],
                'waktu_treatment' => $validatedBooking['waktu_treatment'],
                'status_booking_treatment' => $validatedBooking['status_booking_treatment'],
                'potongan_harga' => $validatedBooking['potongan_harga'] ?? 0,
                'harga_total' => 0,
                'harga_akhir_treatment' => 0,
            ]);
    
            $hargaTotal = 0;
    
            foreach ($validatedBooking['details'] as $detail) {

                $treatment = \App\Models\Treatment::find($detail['id_treatment']);
            
                if (!$treatment) {
                    throw new \Exception("Treatment ID {$detail['id_treatment']} not found");
                }
            
                if (!isset($treatment->biaya_treatment)) {
                    throw new \Exception("Treatment ID {$detail['id_treatment']} does not have a valid 'biaya_treatment'");
                }
            
                $biayaTreatment = $treatment->biaya_treatment;
            
                $detail['id_booking_treatment'] = $booking->id_booking_treatment;
                $detail['biaya_treatment'] = $biayaTreatment;
            
                DetailBookingTreatment::create($detail);
            
                $hargaTotal += $biayaTreatment;
            }
    
            $hargaAkhir = $hargaTotal - $validatedBooking['potongan_harga'];
    
            $booking->update([
                'harga_total' => $hargaTotal,
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
