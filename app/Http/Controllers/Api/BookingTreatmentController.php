<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookingTreatment;
use Illuminate\Http\Request;

class BookingTreatmentController extends Controller
{
    public function index()
    {
        return BookingTreatment::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'required|exists:users,id_user',
            'waktu_treatment' => 'required|date',
            'status_booking_treatment' => 'required|string|max:255',
        ]);

        $booking = BookingTreatment::create($validated);

        return response()->json($booking, 201);
    }

    public function show($id)
    {
        $booking = BookingTreatment::find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking Treatment not found'], 404);
        }

        return response()->json($booking);
    }

    public function update(Request $request, $id)
    {
        $booking = BookingTreatment::find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking Treatment not found'], 404);
        }

        $validated = $request->validate([
            'id_user' => 'exists:users,id_user',
            'waktu_treatment' => 'date',
            'status_booking_treatment' => 'string|max:255',
        ]);

        $booking->update($validated);

        return response()->json($booking);
    }

    public function destroy($id)
    {
        $booking = BookingTreatment::find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking Treatment not found'], 404);
        }

        $booking->delete();

        return response()->json(['message' => 'Booking Treatment deleted successfully']);
    }
}
