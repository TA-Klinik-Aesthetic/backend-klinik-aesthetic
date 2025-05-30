<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Konsultasi;
use App\Models\BookingTreatment;

class RekamMedisController extends Controller
{
    public function index()
    {
        // Ambil seluruh data user
        $users = User::all();

        // Inisialisasi array untuk data rekam medis
        $medicalRecords = [];

        // Loop untuk menghitung total konsultasi dan total booking treatment untuk setiap user
        foreach ($users as $user) {
            $totalKonsultasi = Konsultasi::where('id_user', $user->id_user)->count();
            $totalBookingTreatment = BookingTreatment::where('id_user', $user->id_user)->count();

            $medicalRecords[] = [
                'user' => $user,
                'total_konsultasi' => $totalKonsultasi,
                'total_booking_treatment' => $totalBookingTreatment,
            ];
        }

        return response()->json($medicalRecords);
    }

    public function show($id_user)
    {
        // Ambil data user berdasarkan ID
        $user = User::findOrFail($id_user);

        // Ambil data konsultasi yang berkaitan dengan user, termasuk dokter
        $konsultasi = Konsultasi::where('id_user', $id_user)
            ->with(['detail_konsultasi', 'detail_konsultasi.treatment', 'dokter'])  // Mengambil detail konsultasi dan dokter
            ->get();

        // Ambil data booking treatment yang berkaitan dengan user, termasuk dokter dan beautician
        $bookingTreatment = BookingTreatment::where('id_user', $id_user)
            ->with(['detailBooking', 'dokter', 'beautician', 'detailBooking.treatment'])  // Mengambil detail booking treatment, dokter, dan beautician
            ->get();

        // Gabungkan data menjadi satu
        $medicalRecord = [
            'user' => $user,
            'konsultasi' => $konsultasi,
            'booking_treatment' => $bookingTreatment,
        ];

        return response()->json($medicalRecord);
    }
}
