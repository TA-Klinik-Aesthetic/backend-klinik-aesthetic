<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Konsultasi;
use App\Models\BookingTreatment;
use App\Models\BookingTreatmentPaket;


class RekamMedisController extends Controller
{
    public function index()
    {
        $users = User::all();
        $medicalRecords = [];

        foreach ($users as $user) {
            $totalKonsultasi = Konsultasi::where('id_user', $user->id_user)
                ->where('status_booking_konsultasi', 'Selesai')
                ->count();

            // Hitung REGULER dan PAKET dari model yang berbeda, lalu dijumlahkan
            $totalReguler = BookingTreatment::where('id_user', $user->id_user)
                ->where('status_booking_treatment', 'Selesai')
                ->count();

            $totalPaket = BookingTreatmentPaket::where('id_user', $user->id_user)
                ->where('status_booking_treatment', 'Selesai')   // ganti jika nama kolom status berbeda
                ->count();

            $medicalRecords[] = [
                'user'                    => $user,
                'total_konsultasi'        => $totalKonsultasi,
                'total_booking_treatment' => $totalReguler + $totalPaket, // ← gabungan reguler+paket
            ];
        }

        return response()->json($medicalRecords);
    }

    public function show($id_user)
    {
        $user = User::findOrFail($id_user);

        // Konsultasi selesai
        $konsultasi = Konsultasi::where('id_user', $id_user)
            ->where('status_booking_konsultasi', 'Selesai')
            ->with(['detail_konsultasi', 'detail_konsultasi.treatment', 'dokter'])
            ->get();

        // Booking REGULER (pakai model BookingTreatment)
        $bookingReguler = BookingTreatment::where('id_user', $id_user)
            ->where('status_booking_treatment', 'Selesai')
            ->with(['detailBooking', 'detailBooking.treatment', 'dokter', 'beautician'])
            ->get();

        // Booking DARI PAKET (pakai model BookingTreatmentPaket)
        $bookingPaket = BookingTreatmentPaket::where('id_user', $id_user)
            ->where('status_booking_treatment', 'Selesai') // ganti jika kolom status berbeda
            // → tambah relasi sesuai modelmu. Contoh (sesuaikan nama relasinya):
            // ->with(['detailBookingPaket', 'detailBookingPaket.treatment', 'dokter', 'beautician',
            //         'penjualanPaketTreatment', 'penjualanPaketTreatment.details.paket'])
            ->get();

        return response()->json([
            'user'               => $user,
            'konsultasi'         => $konsultasi,
            'booking_treatment'  => [
                'total'   => $bookingReguler->count() + $bookingPaket->count(), // ← total gabungan
                'reguler' => $bookingReguler,                                   // ← detail reguler
                'paket'   => $bookingPaket,                                     // ← detail paket
            ],
        ]);
    }
}
