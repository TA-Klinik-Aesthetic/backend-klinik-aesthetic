<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JadwalTreatment;

class JadwalTreatmentController extends Controller
{
    public function showByDate($tanggal)
    {
        // Validasi format tanggal
        if (! preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $tanggal)) {
            return response()->json([
                'success' => false,
                'message' => 'Format tanggal harus YYYY-MM-DD'
            ], 422);
        }

        // Cari master jadwal
        $jadwal = JadwalTreatment::with('details')
            ->where('tanggal_treatment', $tanggal)
            ->first();

        if (! $jadwal) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'tanggal_treatment' => $tanggal,
                    'details'          => []
                ]
            ]);
        }

        // Format data output
        $data = [
            'tanggal_treatment' => $jadwal->tanggal_treatment,
            'details' => $jadwal->details->map(fn($d) => [
                'id_detail'       => $d->id_detail_jadwal_treatment,
                'waktu_tersedia'  => $d->waktu_tersedia,
                'maks_booking'    => $d->maks_booking,
                'status_jadwal'   => $d->status_jadwal,
                // tambahkan count booking jika relasi ada
                // 'booked_count' => $d->bookings()->count(),
            ])
        ];

        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }
}
