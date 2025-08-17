<?php

namespace App\Http\Controllers;

use App\Models\BookingTreatmentPaket;
use App\Models\DetailBookingTreatmentPaket;
use App\Models\PaketTreatmentPelanggan;
use App\Models\DetailPaketTreatmentPelanggan;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BookingTreatmentPaketController extends Controller
{
    /**
     * GET /api/booking-treatment-paket
     * List semua booking paket (+ ringkas relasi)
     */
    public function index()
    {
        $list = BookingTreatmentPaket::with([
            'user:id_user,nama_user',
            'dokter:id_dokter,nama_dokter',
            'beautician:id_beautician,nama_beautician',
            'details:id_detail_booking_treatment_paket,id_booking_treatment_paket,id_paket_treatment_pelanggan,id_treatment,jumlah_dipakai',
            'details.treatment:id_treatment,nama_treatment',
            'details.paketPelanggan:id_paket_treatment_pelanggan,id_paket_treatment',
            'details.paketPelanggan.paket:id_paket_treatment,nama_paket_treatment',
        ])
            ->orderByDesc('id_booking_treatment_paket')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $list,
        ]);
    }

    /**
     * GET /api/booking-treatment-paket/{id}
     * Detail booking paket
     */
    public function show($id)
    {
        $booking = BookingTreatmentPaket::with([
            'user:id_user,nama_user',
            'dokter:id_dokter,nama_dokter',
            'beautician:id_beautician,nama_beautician',
            'details:id_detail_booking_treatment_paket,id_booking_treatment_paket,id_paket_treatment_pelanggan,id_treatment,jumlah_dipakai',
            'details.treatment:id_treatment,nama_treatment',
            'details.paketPelanggan:id_paket_treatment_pelanggan,id_user,id_paket_treatment',
            'details.paketPelanggan.paket:id_paket_treatment,nama_paket_treatment',
        ])->find($id);

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking treatment paket tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $booking,
        ]);
    }

    /**
     * POST /api/booking-treatment-paket
     * Simpan booking paket + detail.
     * - Kurangi kuota di tb_detail_paket_treatment_pelanggan (lockForUpdate).
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_user'         => ['required', 'integer', 'exists:tb_user,id_user'],
            'waktu_treatment' => ['required', 'date'],
            'id_dokter'       => ['nullable', 'integer', 'exists:tb_dokter,id_dokter'],
            'id_beautician'   => ['nullable', 'integer', 'exists:tb_beautician,id_beautician'],
            'details'                              => ['required', 'array', 'min:1'],
            'details.*.id_paket_treatment_pelanggan' => ['required', 'integer', 'exists:tb_paket_treatment_pelanggan,id_paket_treatment_pelanggan'],
            'details.*.id_treatment'               => ['required', 'integer', 'exists:tb_treatment,id_treatment'],
            'details.*.jumlah_dipakai'             => ['nullable', 'integer', 'min:1'],
        ]);

        // Cegah (ptp_id,treatment_id) duplikat dalam satu request
        $pairs = [];
        foreach ($request->details as $i => $d) {
            $k = $d['id_paket_treatment_pelanggan'] . '-' . $d['id_treatment'];
            if (isset($pairs[$k])) {
                return response()->json([
                    'success' => false,
                    'message' => "Detail duplikat pada baris " . ($i + 1) . " (paket pelanggan & treatment sama).",
                ], 422);
            }
            $pairs[$k] = true;
        }

        DB::beginTransaction();
        try {
            $status = (!empty($request->id_dokter) || !empty($request->id_beautician))
                ? 'Berhasil dibooking'
                : 'Verifikasi';

            $booking = BookingTreatmentPaket::create([
                'id_user'                  => $request->id_user,
                'waktu_treatment'          => $request->waktu_treatment,
                'id_dokter'                => $request->id_dokter,
                'id_beautician'            => $request->id_beautician,
                'status_booking_treatment' => $status,
            ]);

            // SIMPAN DETAIL SAJA (tanpa kurangi kuota)
            foreach ($request->details as $d) {
                DetailBookingTreatmentPaket::create([
                    'id_booking_treatment_paket'   => $booking->id_booking_treatment_paket,
                    'id_paket_treatment_pelanggan' => $d['id_paket_treatment_pelanggan'],
                    'id_treatment'                 => $d['id_treatment'],
                    'jumlah_dipakai'               => $d['jumlah_dipakai'] ?? 1,
                ]);
            }

            DB::commit();

            $fresh = BookingTreatmentPaket::with([
                'user:id_user,nama_user',
                'dokter:id_dokter,nama_dokter',
                'beautician:id_beautician,nama_beautician',
                'details:id_detail_booking_treatment_paket,id_booking_treatment_paket,id_paket_treatment_pelanggan,id_treatment,jumlah_dipakai',
                'details.treatment:id_treatment,nama_treatment',
                'details.paketPelanggan:id_paket_treatment_pelanggan,id_paket_treatment',
                'details.paketPelanggan.paket:id_paket_treatment,nama_paket_treatment',
            ])->find($booking->id_booking_treatment_paket);

            return response()->json([
                'success' => true,
                'message' => 'Booking treatment paket berhasil dibuat.',
                'data'    => $fresh,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat booking treatment paket.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * PUT /api/booking-treatment-paket/{id}
     * Update dokter/beautician
     */
    public function update(Request $request, $id)
    {
        $booking = BookingTreatmentPaket::find($id);
        if (! $booking) {
            return response()->json(['success' => false, 'message' => 'Booking tidak ditemukan.'], 404);
        }

        $data = $request->validate([
            'id_dokter'     => ['nullable', 'integer', 'exists:tb_dokter,id_dokter'],
            'id_beautician' => ['nullable', 'integer', 'exists:tb_beautician,id_beautician'],
        ]);

        $booking->update($data);

        // Jika salah satu terisi → set Berhasil dibooking (kalau masih Verifikasi)
        if (($booking->id_dokter || $booking->id_beautician) && $booking->status_booking_treatment === 'Verifikasi') {
            $booking->status_booking_treatment = 'Berhasil dibooking';
            // boleh catat waktu mulai jika kamu ingin start di sini:
            if (is_null($booking->treatment_mulai)) {
                $booking->treatment_mulai = now();
            }
            $booking->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil diperbarui.',
            'data'    => $booking,
        ]);
    }

    /**
     * PUT /api/booking-treatment-paket/{id}/status
     * Update status booking (enum: Treatment dimulai, Dibatalkan, Selesai)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_booking_treatment' => ['required', Rule::in(['Treatment dimulai', 'Selesai', 'Dibatalkan'])],
        ]);

        DB::beginTransaction();
        try {
            // Ambil booking + detail, kunci baris untuk konsistensi
            $booking = BookingTreatmentPaket::with(['details'])
                ->lockForUpdate()
                ->find($id);

            if (! $booking) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Booking tidak ditemukan.'], 404);
            }

            $from = $booking->status_booking_treatment;
            $to   = $request->status_booking_treatment;

            // Validasi alur status sederhana:
            // - hanya bisa batal kalau belum Selesai
            // - Selesai hanya dari Treatment dimulai
            // - Treatment dimulai tidak boleh dari Selesai/Dibatalkan
            if ($to === 'Dibatalkan' && $from === 'Selesai') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Booking yang sudah selesai tidak bisa dibatalkan.'
                ], 422);
            }

            if ($to === 'Treatment dimulai') {
                if (in_array($from, ['Selesai', 'Dibatalkan'], true)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Tidak dapat memulai treatment dari status '{$from}'."
                    ], 422);
                }
                // set waktu mulai jika belum ada
                if (is_null($booking->treatment_mulai)) {
                    $booking->treatment_mulai = now();
                }
            }

            if ($to === 'Selesai') {
                if ($from !== 'Treatment dimulai') {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Status hanya bisa diubah ke "Selesai" jika status saat ini adalah "Treatment dimulai".'
                    ], 422);
                }

                // set waktu selesai jika belum ada
                if (is_null($booking->treatment_selesai)) {
                    $booking->treatment_selesai = now();
                }

                // 🔻 Kurangi kuota paket milik pelanggan untuk setiap detail
                foreach ($booking->details as $det) {
                    $qty = (int)($det->jumlah_dipakai ?? 1);

                    $kuota = DetailPaketTreatmentPelanggan::where('id_paket_treatment_pelanggan', $det->id_paket_treatment_pelanggan)
                        ->where('id_treatment', $det->id_treatment)
                        ->lockForUpdate()
                        ->first();

                    if ($kuota->jumlah_penggunaan < $qty) {
                        throw new \Exception("Kuota tidak mencukupi untuk treatment ID {$det->id_treatment}. Tersisa: {$kuota->jumlah_penggunaan}, dibutuhkan: {$qty}.");
                    }

                    $kuota->jumlah_penggunaan -= $qty;
                    $kuota->save();
                }
            }

            // Update status & simpan perubahan waktu
            $booking->status_booking_treatment = $to;
            $booking->save();

            DB::commit();

            // opsional: reload relasi biar fresh
            $booking->load(['user:id_user,nama_user', 'dokter:id_dokter,nama_dokter', 'beautician:id_beautician,nama_beautician', 'details.treatment:id_treatment,nama_treatment']);

            return response()->json([
                'success' => true,
                'message' => 'Status booking treatment paket berhasil diperbarui.',
                'data'    => $booking,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
