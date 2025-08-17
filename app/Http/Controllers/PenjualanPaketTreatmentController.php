<?php

namespace App\Http\Controllers;

use App\Models\PaketTreatment;
use App\Models\PenjualanPaketTreatment;
use App\Models\DetailPenjualanPaketTreatment;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Exception;

class PenjualanPaketTreatmentController extends Controller
{
    public function index()
    {
        // Ambil semua penjualan paket + relasi penting
        $penjualan = PenjualanPaketTreatment::with([
            // detail dan nama paketnya
            'details:id_detail_penjualan_paket_treatment,id_penjualan_paket_treatment,id_paket_treatment,harga_paket_treatment',
            'details.paket:id_paket_treatment,nama_paket_treatment,harga_paket_treatment',
            // user pembeli
            'user:id_user,nama_user',
            // promo (kalau ada)
            'promo:id_promo,nama_promo,tipe_potongan,potongan_harga,minimal_belanja',
            // pembayaran (kalau sudah dibuat)
            'pembayaran:id_pembayaran,id_penjualan_paket_treatment,metode_pembayaran,status_pembayaran,waktu_pembayaran,uang,kembalian',
        ])
        ->orderBy('id_penjualan_paket_treatment', 'asc') // atau 'desc' kalau mau terbaru dulu
        ->get();

        return response()->json($penjualan);
    }

    /** GET /api/penjualan-paket/{id} */
    public function show($id)
    {
        $penjualan = PenjualanPaketTreatment::with([
            'details:id_detail_penjualan_paket_treatment,id_penjualan_paket_treatment,id_paket_treatment,harga_paket_treatment',
            'details.paket:id_paket_treatment,nama_paket_treatment,harga_paket_treatment',
            'user:id_user,nama_user,no_telp,email',
            'promo:id_promo,nama_promo,tipe_potongan,potongan_harga,minimal_belanja',
            'pembayaran:id_pembayaran,id_penjualan_paket_treatment,metode_pembayaran,status_pembayaran,waktu_pembayaran,uang,kembalian,gambar_bukti_pembayaran',
        ])->find($id);

        if (! $penjualan) {
            return response()->json(['error' => 'Data penjualan paket treatment tidak ditemukan'], 404);
        }

        return response()->json($penjualan);
    }

    /**
     * POST /api/penjualan-paket/kasir
     * Body:
     * {
     *   "id_user": 123,
     *   "paket": [
     *     {"id_paket_treatment": 1, "jumlah": 2},
     *     {"id_paket_treatment": 3, "jumlah": 1}
     *   ],
     *   "id_promo": 5 // optional
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_user'  => ['required','integer','exists:tb_user,id_user'],
            'paket'    => ['required','array','min:1'],
            // utamakan key baru: id__paket_treatment (double underscore)
            'paket.*.id__paket_treatment' => [
                'required_without:paket.*.id_paket_treatment',
                'integer',
                Rule::exists('tb_paket_treatment','id_paket_treatment')
            ],
            // tetap terima key lama (opsional)
            'paket.*.id_paket_treatment' => [
                'sometimes',
                'integer',
                Rule::exists('tb_paket_treatment','id_paket_treatment')
            ],
            'id_promo' => ['nullable','integer','exists:tb_promo,id_promo'],
        ]);
    
        DB::beginTransaction();
        try {
            // flatten ke array integer id paket (duplikasi tetap dipertahankan)
            $paketIds = collect($request->input('paket', []))
                ->map(function ($row) {
                    return $row['id__paket_treatment'] ?? $row['id_paket_treatment'] ?? null;
                })
                ->filter() // buang null
                ->values();
    
            if ($paketIds->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Daftar paket tidak boleh kosong.',
                ], 422);
            }
    
            // total & detail (ambil harga paket dari master)
            $hargaTotal = 0;
            $detailRows = [];
    
            foreach ($paketIds as $pid) {
                $paket = PaketTreatment::select('id_paket_treatment','harga_paket_treatment')
                            ->findOrFail($pid);
    
                $hargaTotal += (float) $paket->harga_paket_treatment;
    
                $detailRows[] = [
                    'id_paket_treatment'   => $paket->id_paket_treatment,
                    'harga_paket_treatment'=> $paket->harga_paket_treatment,
                ];
            }
    
            // hitung promo (sementara memakai promo jenis "Treatment")
            $potonganUntukSimpan = 0;
            $potonganHitung      = 0;
            if ($request->filled('id_promo')) {
                $promo = Promo::findOrFail($request->id_promo);
    
                if ($promo->jenis_promo !== 'Treatment') {
                    throw new \Exception("Promo yang digunakan bukan untuk penjualan paket/treatment.");
                }
                if (!is_null($promo->minimal_belanja) && $hargaTotal < $promo->minimal_belanja) {
                    throw new \Exception(
                        "Promo tidak dapat digunakan karena total belanja kurang dari minimal belanja sebesar Rp" .
                        number_format($promo->minimal_belanja, 0, ',', '.')
                    );
                }
    
                $potonganUntukSimpan = $promo->potongan_harga;
                $potonganHitung = $promo->tipe_potongan === 'Diskon'
                    ? ($hargaTotal * $promo->potongan_harga) / 100
                    : $promo->potongan_harga;
            }
    
            // pajak 10%
            $subtotalSetelahDiskon = $hargaTotal - $potonganHitung;
            $pajakHitung           = ($subtotalSetelahDiskon * 10) / 100;
            $hargaAkhir            = $subtotalSetelahDiskon + $pajakHitung;
    
            // simpan header penjualan paket
            $penjualan = PenjualanPaketTreatment::create([
                'id_user'           => $request->id_user,
                'tanggal_pembelian' => now(),
                'harga_total'       => $hargaTotal,
                'id_promo'          => $request->id_promo,
                'potongan_harga'    => $potonganUntukSimpan,
                'besaran_pajak'     => $pajakHitung,
                'harga_akhir'       => $hargaAkhir,
            ]);
    
            // simpan detail penjualan
            foreach ($detailRows as $d) {
                DetailPenjualanPaketTreatment::create([
                    'id_penjualan_paket_treatment' => $penjualan->id_penjualan_paket_treatment,
                    'id_paket_treatment'           => $d['id_paket_treatment'],
                    'harga_paket_treatment'        => $d['harga_paket_treatment'],
                ]);
            }
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Penjualan paket treatment berhasil disimpan',
                'data'    => $penjualan,
            ], 201);
    
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
