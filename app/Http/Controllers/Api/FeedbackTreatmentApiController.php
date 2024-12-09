<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeedbackTreatment;
use Illuminate\Http\Request;

class FeedbackTreatmentApiController extends Controller
{

    public function index()
    {
        $feedbacks = FeedbackTreatment::with('bookingTreatment')->get();
        return response()->json($feedbacks, 200);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_booking_treatment' => 'required|exists:tb_booking_treatment,id_booking_treatment',
            'rating' => 'required|integer|min:1|max:5',
            'teks_feedback' => 'required|string',
            'balasan_feedback' => 'nullable|string',
        ]);

        $feedback = FeedbackTreatment::create($validatedData);
        return response()->json($feedback, 201);
    }

    public function show($id)
    {
        $feedback = FeedbackTreatment::with('bookingTreatment')->find($id);

        if (!$feedback) {
            return response()->json(['message' => 'Feedback not found'], 404);
        }

        return response()->json($feedback, 200);
    }

    public function update(Request $request, $id)
    {
        $feedback = FeedbackTreatment::find($id);

        if (!$feedback) {
            return response()->json(['message' => 'Feedback not found'], 404);
        }

        $validatedData = $request->validate([
            'id_booking_treatment' => 'sometimes|exists:tb_booking_treatment,id_booking_treatment',
            'rating' => 'sometimes|integer|min:1|max:5',
            'teks_feedback' => 'sometimes|string',
            'balasan_feedback' => 'nullable|string',
        ]);

        $feedback->update($validatedData);
        return response()->json($feedback, 200);
    }

    public function destroy($id)
    {
        $feedback = FeedbackTreatment::find($id);

        if (!$feedback) {
            return response()->json(['message' => 'Feedback not found'], 404);
        }

        $feedback->delete();
        return response()->json(['message' => 'Feedback deleted successfully'], 200);
    }
}
