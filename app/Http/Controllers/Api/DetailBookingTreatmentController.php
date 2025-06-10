<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailBookingTreatment;
use App\Models\DetailBookingProduk;
use App\Models\BookingTreatment;
use App\Models\Promo;
use App\Models\Treatment;
use App\Models\Produk;
use App\Models\KompensasiDiberikan;
use App\Models\Komplain;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DetailBookingTreatmentController extends Controller
{
    public function index()
    {
        // Mengambil seluruh data booking treatment dengan relasi ke user, promo, detail booking, dokter, beautician, dan treatment
        $bookingTreatments = BookingTreatment::with([
            'user',
            'dokter',
            'beautician',
            'promo',
            'detailBooking.treatment',
        ])->get();

        // Cek jika data ditemukan
        if ($bookingTreatments->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data booking treatment',
            ], 404);
        }

        // Mengembalikan data booking treatment dengan status 200 OK
        return response()->json([
            'booking_treatments' => $bookingTreatments,
            'message' => 'Data booking treatment berhasil diambil',
        ], 200);
    }

    // public function store(Request $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         // Validasi input booking
    //         $validatedBooking = $request->validate([
    //             'id_user' => 'required|exists:tb_user,id_user',
    //             'waktu_treatment' => 'required|date',
    //             'status_booking_treatment' => 'required|string',
    //             'id_promo' => 'nullable|exists:tb_promo,id_promo',
    //             'details' => 'required|array',
    //             'details.*.id_treatment' => 'required|exists:tb_treatment,id_treatment',
    //             'details.*.id_dokter' => 'nullable|exists:tb_dokter,id_dokter',
    //             'details.*.id_beautician' => 'nullable|exists:tb_beautician,id_beautician',
    //             'details.*.produk' => 'nullable|array',
    //             'details.*.produk.*.id_produk' => 'nullable|exists:tb_produk,id_produk',
    //             'details.*.produk.*.jumlah_produk' => 'nullable|integer|min:1'
    //         ]);

    //         // Membuat Booking Treatment
    //         $booking = BookingTreatment::create([
    //             'id_user' => $validatedBooking['id_user'],
    //             'waktu_treatment' => $validatedBooking['waktu_treatment'],
    //             'status_booking_treatment' => $validatedBooking['status_booking_treatment'],
    //             'id_promo' => $validatedBooking['id_promo'],
    //             'harga_total' => 0,
    //             'harga_akhir_treatment' => 0,
    //             'potongan_harga' => 0,
    //         ]);

    //         $hargaTotal = 0;

    //         // Memasukkan detail booking treatment (lebih dari satu treatment)
    //         foreach ($validatedBooking['details'] as $detail) {
    //             $treatment = Treatment::findOrFail($detail['id_treatment']);
    //             $biayaTreatment = $treatment->biaya_treatment;

    //             $detailBooking = DetailBookingTreatment::create([
    //                 'id_booking_treatment' => $booking->id_booking_treatment,
    //                 'id_treatment' => $treatment->id_treatment,
    //                 'biaya_treatment' => $biayaTreatment,
    //                 'id_dokter' => $detail['id_dokter'] ?? null,
    //                 'id_beautician' => $detail['id_beautician'] ?? null,
    //             ]);

    //             $hargaTotal += $biayaTreatment;

    //             // Jika ada produk, tambahkan ke detail booking produk
    //             if (!empty($detail['produk'])) {
    //                 foreach ($detail['produk'] as $produkDetail) {
    //                     $produk = Produk::findOrFail($produkDetail['id_produk']);

    //                     // Validasi: Produk harus sesuai dengan jenis treatment
    //                     if ($produk->id_jenis_treatment !== $treatment->id_jenis_treatment) {
    //                         return response()->json([
    //                             'message' => "Produk ID {$produk->id_produk} tidak sesuai dengan jenis treatment ID {$treatment->id_treatment}."
    //                         ], 422);
    //                     }

    //                     DetailBookingProduk::create([
    //                         'id_detail_booking_treatment' => $detailBooking->id_detail_booking_treatment,
    //                         'id_produk' => $produk->id_produk,
    //                         'jumlah_produk' => $produkDetail['jumlah_produk'] ?? null,
    //                         'harga_produk' => $produk->harga_produk,
    //                         'harga_total_produk' => $produk->harga_produk * $produkDetail['jumlah_produk'] // Perhitungan total harga produk
    //                     ]);

    //                     // Kurangi stok produk sesuai jumlah yang dipesan
    //                     $produk->decrement('stok_produk', $produkDetail['jumlah_produk']);

    //                     $hargaTotal += $produk->harga_produk * $produkDetail['jumlah_produk'];
    //                 }
    //             }
    //         }

    //         // Mengambil promo berdasarkan id_promo
    //         $promo = Promo::find($validatedBooking['id_promo']);
    //         $potonganHarga = 0;

    //         if ($promo) {
    //             $potonganHarga = $promo->potongan_harga;
    //         }

    //         // Hitung harga akhir treatment setelah diskon
    //         $hargaAkhir = max($hargaTotal - $potonganHarga, 0);

    //         // Update harga total, potongan harga, dan harga akhir treatment
    //         $booking->update([
    //             'harga_total' => $hargaTotal,
    //             'potongan_harga' => $potonganHarga,
    //             'harga_akhir_treatment' => $hargaAkhir
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Booking berhasil!',
    //             'booking_treatment' => $booking
    //         ], 201);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function show($id)
    {
        // Mencari data booking treatment berdasarkan ID dengan relasi lengkap
        $bookingTreatment = BookingTreatment::with([
            'user',
            'dokter',
            'beautician',
            'promo',
            'detailBooking.treatment',
        ])->find($id);

        // Cek jika data booking treatment tidak ditemukan
        if (!$bookingTreatment) {
            return response()->json([
                'message' => 'Booking treatment tidak ditemukan',
            ], 404);
        }

        // Mengembalikan data booking treatment beserta detail booking treatment
        return response()->json([
            'booking_treatment' => $bookingTreatment,
            'message' => 'Detail booking treatment berhasil diambil',
        ], 200);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validasi input booking
            $validatedBooking = $request->validate([
                'id_user' => 'required|exists:tb_user,id_user',
                'waktu_treatment' => 'required|date',
                'id_dokter' => 'nullable|exists:tb_dokter,id_dokter',
                'id_beautician' => 'nullable|exists:tb_beautician,id_beautician',
                'status_booking_treatment' => 'required|string',
                'id_promo' => 'nullable|exists:tb_promo,id_promo',  // Validasi id_promo
                'details' => 'required|array',
                'details.*.id_treatment' => 'required|exists:tb_treatment,id_treatment',
                'details.*.id_kompensasi_diberikan' => 'nullable|exists:tb_kompensasi_diberikan,id_kompensasi_diberikan',
            ]);

            // Memastikan promo tidak bisa diisi jika semua treatment menggunakan kompensasi
            $allTreatmentsHaveCompensation = collect($validatedBooking['details'])->every(function ($detail) {
                return !empty($detail['id_kompensasi_diberikan']);
            });

            // Jika semua treatment memiliki kompensasi, pastikan promo adalah null
            if ($allTreatmentsHaveCompensation && !is_null($validatedBooking['id_promo'])) {
                throw new \Exception('Promo tidak dapat digunakan jika seluruh treatment menggunakan kompensasi.');
            }

            // Membuat Booking Treatment
            $booking = BookingTreatment::create([
                'id_user' => $validatedBooking['id_user'],
                'waktu_treatment' => $validatedBooking['waktu_treatment'],
                'id_dokter' => $validatedBooking['id_dokter'],
                'id_beautician' => $validatedBooking['id_beautician'],
                'status_booking_treatment' => $validatedBooking['status_booking_treatment'],
                'harga_total' => 0,
                'id_promo' => $validatedBooking['id_promo'],  // Menyimpan id_promo
                'potongan_harga' => 0,  // Awalnya potongan_harga di-set 0
                'besaran_pajak' => 0,    // pajak tetap 10%
                'harga_akhir_treatment' => 0,

            ]);

            $hargaTotal = 0;

            // Memasukkan detail booking treatment (lebih dari satu treatment)
            foreach ($validatedBooking['details'] as $detail) {
                $treatment = Treatment::find($detail['id_treatment']);
                if (!$treatment) {
                    throw new \Exception("Treatment ID {$detail['id_treatment']} not found");
                }

                $biayaTreatment = $treatment->biaya_treatment;

                // Jika menggunakan kompensasi
                if (!empty($detail['id_kompensasi_diberikan'])) {
                    // Ambil kompensasi diberikan + relasi kompensasi ➝ komplain & treatment
                    $kompensasiDiberikan = KompensasiDiberikan::with(['komplain', 'kompensasi.treatment'])
                        ->where('id_kompensasi_diberikan', $detail['id_kompensasi_diberikan'])
                        ->where('status_kompensasi', 'Belum digunakan')
                        ->first();

                    if (!$kompensasiDiberikan) {
                        throw new \Exception("Kompensasi tidak tersedia atau sudah digunakan.");
                    }

                    // Validasi user dan treatment dari relasi
                    if (
                        $kompensasiDiberikan->komplain->id_user != $validatedBooking['id_user'] ||
                        $kompensasiDiberikan->kompensasi->treatment->id_treatment != $detail['id_treatment']
                    ) {
                        throw new \Exception("Kompensasi tidak valid untuk user atau treatment ini.");
                    }

                    // Set biaya menjadi 0 dan tandai sebagai digunakan
                    $biayaTreatment = 0;
                    $kompensasiDiberikan->update([
                        'status_kompensasi' => 'Sudah digunakan',
                        'tanggal_pemakaian_kompensasi' => now(),
                    ]);
                }

                // Simpan detail
                $detail['id_booking_treatment'] = $booking->id_booking_treatment;
                $detail['biaya_treatment'] = $biayaTreatment;

                DetailBookingTreatment::create($detail);
                $hargaTotal += $biayaTreatment;
            }

            // Mengambil promo berdasarkan id_promo
            $promo = Promo::find($validatedBooking['id_promo']);
            // $potonganHarga = 0;
            $nilaiPotonganUntukDisimpan = 0; // ini akan disimpan di kolom potongan_harga
            $nilaiDiskonDihitung = 0;        // ini untuk menghitung pengurangan harga

            if ($promo) {
                // Cek apakah jenis promo adalah Treatment
                if ($promo->jenis_promo !== 'Treatment') {
                    throw new \Exception("Promo yang digunakan bukan jenis Treatment.");
                }

                // Validasi minimal belanja jika ada
                if (!is_null($promo->minimal_belanja) && $hargaTotal < $promo->minimal_belanja) {
                    throw new \Exception("Promo tidak dapat digunakan karena total belanja kurang dari minimal belanja sebesar Rp" . number_format($promo->minimal_belanja, 0, ',', '.'));
                }

                // Simpan apa yang ada di tabel promo ke kolom potongan_harga
                $nilaiPotonganUntukDisimpan = $promo->potongan_harga;

                // Kalau tipe potongan “Diskon”, hitung persentase
                if ($promo->tipe_potongan === 'Diskon') {
                    // Contoh: kalau potongan_harga = 75 (75%), maka:
                    $nilaiDiskonDihitung = ($hargaTotal * $promo->potongan_harga) / 100;
                }
                // Kalau “Rupiah”, maka diskon langsung = potongan_harga
                else {
                    $nilaiDiskonDihitung = $promo->potongan_harga;
                }
            }

            // 6) Hitung pajak 10%
            $subtotalSetelahDiskon = $hargaTotal - $nilaiDiskonDihitung;
            if ($subtotalSetelahDiskon < 0) {
                $subtotalSetelahDiskon = 0;
            }
            $pajakHitung = ($subtotalSetelahDiskon * 10) / 100;

            // 7) Harga akhir
            $hargaAkhir = $subtotalSetelahDiskon + $pajakHitung;

            // 8) Update header
            $booking->update([
                'harga_total'            => $hargaTotal,
                'potongan_harga'         => $nilaiPotonganUntukDisimpan,
                'besaran_pajak'                 => $pajakHitung,
                'harga_akhir_treatment'  => $hargaAkhir,
            ]);

            // ✨ Baru: Buat record pembayaran dengan FK otomatis
            Pembayaran::create([
                'id_booking_treatment' => $booking->id_booking_treatment,
                'id_penjualan_produk'  => null,
                'uang'                 => null,
                'kembalian'            => null,
                'metode_pembayaran'    => 'Tunai',
                'status_pembayaran'    => 'Belum Dibayar',
                'waktu_pembayaran'     => null,
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



    public function update(Request $request, $id)
    {
        // Cari detail booking treatment berdasarkan ID
        $bookingTreatment = BookingTreatment::find($id);

        if (!$bookingTreatment) {
            return response()->json(['message' => 'Booking Treatment not found'], 404);
        }

        // Validasi input untuk data dokter dan beautician
        $validated = $request->validate([
            'id_dokter' => 'exists:tb_dokter,id_dokter|nullable',
            'id_beautician' => 'exists:tb_beautician,id_beautician|nullable',
        ]);

        // Update data detail booking treatment
        $bookingTreatment->update($validated);

        return response()->json([
            'booking_treatment' => $bookingTreatment,
            'message' => 'Booking Treatment updated successfully',
        ]);
    }

    public function updateStatusBooking(Request $request, $id)
    {
        // Validasi input untuk status booking treatment
        $validated = $request->validate([
            'status_booking_treatment' => 'string|nullable',
        ]);

        // Cari booking treatment berdasarkan ID
        $bookingTreatment = BookingTreatment::find($id);

        if (!$bookingTreatment) {
            return response()->json(['message' => 'Booking Treatment not found'], 404);
        }

        // Update status booking treatment
        $bookingTreatment->update($validated);

        return response()->json([
            'booking_treatment' => $bookingTreatment,
            'message' => 'Status Booking Treatment updated successfully',
        ]);
    }

    public function indexDetail()
    {
        $details = DetailBookingTreatment::with(['treatment'])->get();

        return response()->json([
            'detail_booking_treatments' => $details,
        ]);
    }

    public function destroy($id)
    {
        $detailBooking = BookingTreatment::find($id);

        if (!$detailBooking) {
            return response()->json(['message' => 'Detail Booking Treatment not found'], 404);
        }

        $detailBooking->delete();

        return response()->json(['message' => 'Detail Booking Treatment deleted successfully']);
    }

    // public function showDetailBookingProduk($id_detail_booking_treatment)
    // {
    //     $detailBookingProduk = DetailBookingProduk::where('id_detail_booking_treatment', $id_detail_booking_treatment)
    //         ->with('produk') // Pastikan ada relasi ke model Produk
    //         ->get();

    //     if ($detailBookingProduk->isEmpty()) {
    //         return response()->json(['message' => 'Data tidak ditemukan'], 404);
    //     }

    //     return response()->json([
    //         'message' => 'Detail booking produk ditemukan',
    //         'data' => $detailBookingProduk
    //     ], 200);
    // }
}
