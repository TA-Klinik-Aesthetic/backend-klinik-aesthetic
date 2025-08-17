<?php

namespace App\Http\Controllers;

use App\Models\PaketTreatment;
use App\Models\PaketTreatmentPelanggan;
use App\Models\DetailPaketTreatmentPelanggan;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaketTreatmentPelangganController extends Controller
{
    public function index(Request $request)
    {
        $q = PaketTreatmentPelanggan::with([
            // hanya info paket & user; TANPA pembayaran
            'paket:id_paket_treatment,nama_paket_treatment',
            'user:id_user,nama_user',
        ])->select([
            'id_paket_treatment_pelanggan',
            'id_user',
            'id_paket_treatment',
        ]);

        if ($request->filled('id_user')) {
            $q->where('id_user', $request->query('id_user'));
        }

        // $data = $q->orderByDesc('id_paket_treatment_pelanggan')->get();

        // urutkan dari id kecil → besar
        $data = $q->orderBy('id_paket_treatment_pelanggan', 'asc')->get();

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    public function show($id)
    {
        $item = PaketTreatmentPelanggan::with([
            'paket:id_paket_treatment,nama_paket_treatment,deskripsi_paket_treatment,harga_paket_treatment',
            'user:id_user,nama_user',
            'details:id_detail_paket_treatment_pelanggan,id_paket_treatment_pelanggan,id_treatment,jumlah_penggunaan',
            'details.treatment:id_treatment,nama_treatment',
        ])->find($id);

        if (! $item) {
            return response()->json([
                'success' => false,
                'message' => 'Paket treatment pelanggan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $item,
        ]);
    }
}
