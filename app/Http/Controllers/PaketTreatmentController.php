<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaketTreatment;
use App\Models\DetailPaketTreatment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaketTreatmentController extends Controller
{
    public function index(Request $request)
    {
        $pakets = PaketTreatment::with([
            'details:id_detail_paket_treatment,id_paket_treatment,id_treatment,jumlah_penggunaan',
            'details.treatment:id_treatment,nama_treatment',
        ])->get();

        return response()->json([
            'success' => true,
            'data'    => $pakets,
        ]);
    }

    public function show($id)
    {
        $paket = PaketTreatment::with([
            'details:id_detail_paket_treatment,id_paket_treatment,id_treatment,jumlah_penggunaan',
            'details.treatment:id_treatment,nama_treatment',
        ])->find($id);
    
        if (! $paket) {
            return response()->json([
                'success' => false,
                'message' => 'Paket treatment tidak ditemukan.',
            ], 404);
        }
    
        return response()->json([
            'success' => true,
            'data'    => $paket,
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_paket_treatment'   => ['required', 'string', 'max:255'],
            'deskripsi_paket_treatment' => ['nullable', 'string'],
            'harga_paket_treatment'  => ['required', 'numeric', 'min:0'],

            'details'                        => ['required', 'array', 'min:1'],
            'details.*.id_treatment'         => ['required', 'integer', 'exists:tb_treatment,id_treatment', 'distinct'],
            'details.*.jumlah_penggunaan'    => ['required', 'integer', 'min:1'],
        ], [
            'details.required' => 'Minimal satu detail treatment harus diisi.',
            'details.*.id_treatment.distinct' => 'Treatment di dalam paket tidak boleh duplikat.',
        ]);

        DB::beginTransaction();
        try {
            // 1) buat paket
            $paket = PaketTreatment::create([
                'nama_paket_treatment'    => $validated['nama_paket_treatment'],
                'deskripsi_paket_treatment' => $validated['deskripsi_paket_treatment'] ?? null,
                'harga_paket_treatment'   => $validated['harga_paket_treatment'],
            ]);

            // 2) buat details (banyak sekaligus)
            $rows = collect($validated['details'])->map(function ($d) use ($paket) {
                return [
                    'id_paket_treatment'  => $paket->id_paket_treatment,
                    'id_treatment'        => $d['id_treatment'],
                    'jumlah_penggunaan'   => $d['jumlah_penggunaan'],
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];
            })->all();

            DetailPaketTreatment::insert($rows);

            DB::commit();

            // kembalikan paket + details
            $paket->load(['details.treatment']);

            return response()->json([
                'success' => true,
                'message' => 'Paket treatment berhasil dibuat.',
                'data'    => $paket,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat paket treatment.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_paket_treatment'       => ['sometimes', 'required', 'string', 'max:255'],
            'deskripsi_paket_treatment'  => ['sometimes', 'nullable', 'string'],
            'harga_paket_treatment'      => ['sometimes', 'required', 'numeric', 'min:0'],

            'details'                        => ['sometimes', 'array', 'min:1'],
            'details.*.id_treatment'         => ['required_with:details', 'integer', 'exists:tb_treatment,id_treatment', 'distinct'],
            'details.*.jumlah_penggunaan'    => ['required_with:details', 'integer', 'min:1'],
        ]);

        DB::beginTransaction();
        try {
            $paket = PaketTreatment::with('details')->findOrFail($id);

            // update field paket (hanya yang dikirim)
            $paket->fill($request->only([
                'nama_paket_treatment',
                'deskripsi_paket_treatment',
                'harga_paket_treatment',
            ]));
            $paket->save();

            // kalau ada details di-request → replace seluruh detail
            if ($request->has('details')) {
                // hapus lama, insert baru
                DetailPaketTreatment::where('id_paket_treatment', $paket->id_paket_treatment)->delete();

                $rows = collect($validated['details'])->map(function ($d) use ($paket) {
                    return [
                        'id_paket_treatment'  => $paket->id_paket_treatment,
                        'id_treatment'        => $d['id_treatment'],
                        'jumlah_penggunaan'   => $d['jumlah_penggunaan'],
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ];
                })->all();

                DetailPaketTreatment::insert($rows);
            }

            DB::commit();

            $paket->load(['details.treatment']);

            return response()->json([
                'success' => true,
                'message' => 'Paket treatment berhasil diperbarui.',
                'data'    => $paket,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui paket treatment.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
