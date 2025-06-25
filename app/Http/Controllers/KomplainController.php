<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Komplain;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use App\Models\DetailBookingTreatment;
use App\Models\BookingTreatment;
// use App\Models\KomplainTreatment;
use App\Models\KompensasiDiberikan;


class KomplainController extends Controller
{
    public function index()
    {
        return response()->json(Komplain::with(['bookingTreatment', 'user'])->get());
    }

    // public function store(Request $request)
    // {
    //     try {
    //         // Validasi input komplain
    //         $validated = $request->validate([
    //             'id_user' => 'required|exists:tb_user,id_user',
    //             'id_booking_treatment' => 'required|exists:tb_booking_treatment,id_booking_treatment', // ID booking treatment yang dipilih
    //             'id_detail_booking_treatment' => 'required|array', // Array dari detail booking treatment yang dikomplain
    //             'id_detail_booking_treatment.*' => 'required|exists:tb_detail_booking_treatment,id_detail_booking_treatment', // Validasi ID detail booking treatment
    //             'teks_komplain' => 'nullable|string',
    //             'gambar_komplain.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',
    //         ]);


    //         // Validasi tambahan: Pastikan setiap id_detail_booking_treatment terkait dengan id_booking_treatment
    //         foreach ($validated['id_detail_booking_treatment'] as $idDetailBookingTreatment) {
    //             $detail = DetailBookingTreatment::find($idDetailBookingTreatment);

    //             // Cek apakah id_detail_booking_treatment terkait dengan id_booking_treatment yang dipilih
    //             if ($detail && $detail->id_booking_treatment != $validated['id_booking_treatment']) {
    //                 return response()->json([
    //                     'message' => 'Detail booking treatment tidak terkait dengan booking treatment yang dipilih',
    //                 ], 422);
    //             }
    //         }



    //         // Simpan gambar komplain jika ada
    //         $gambarPaths = [];
    //         if ($request->hasFile('gambar_komplain')) {
    //             foreach ($request->file('gambar_komplain') as $file) {
    //                 $fileName = time() . '_' . $file->getClientOriginalName();
    //                 $path = $file->storeAs('komplain_images', $fileName, 'public');
    //                 if ($path) {
    //                     $gambarPaths[] = $path;
    //                 }
    //             }
    //         }


    //         // Simpan data komplain ke dalam database
    //         $validated['gambar_komplain'] = json_encode($gambarPaths); // Menyimpan array gambar dalam format JSON

    //         // Simpan komplain
    //         $komplain = Komplain::create($validated);

    //         // Menyimpan data detail_booking_treatment yang dikomplain
    //         foreach ($validated['id_detail_booking_treatment'] as $idDetailBookingTreatment) {
    //             KomplainTreatment::create([
    //                 'id_komplain' => $komplain->id_komplain,
    //                 'id_detail_booking_treatment' => $idDetailBookingTreatment,
    //             ]);
    //         }

    //         return response()->json([
    //             'message' => 'Komplain berhasil ditambahkan',
    //             'data' => $komplain,
    //         ], 201);
    //     } catch (ValidationException $e) {
    //         return response()->json(['message' => 'Validasi data gagal', 'errors' => $e->errors()], 422);
    //     } catch (QueryException $e) {
    //         return response()->json(['message' => 'Terjadi kesalahan saat menyimpan komplain', 'error' => $e->getMessage()], 500);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'Terjadi kesalahan yang tidak terduga', 'error' => $e->getMessage()], 500);
    //     }
    // }

    public function store(Request $request)
    {
        try {
            // Validasi input komplain
            $validated = $request->validate([
                'id_user' => 'required|exists:tb_user,id_user',
                'id_booking_treatment' => 'required|exists:tb_booking_treatment,id_booking_treatment', // ID booking treatment yang dipilih
                'id_detail_booking_treatment' => 'required|exists:tb_detail_booking_treatment,id_detail_booking_treatment', // Satu detail booking treatment
                'teks_komplain' => 'nullable|string',
                'gambar_komplain.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            ]);

            // 2) Ambil booking treatment dan cek kepemilikan user
            $booking = BookingTreatment::find($validated['id_booking_treatment']);
            if (!$booking || $booking->id_user != $validated['id_user']) {
                return response()->json([
                    'message' => 'User tidak berhak membuat komplain untuk booking treatment ini',
                ], 422);
            }

            // Validasi tambahan: Pastikan id_detail_booking_treatment terkait dengan id_booking_treatment
            $detail = DetailBookingTreatment::find($validated['id_detail_booking_treatment']);

            // Cek apakah id_detail_booking_treatment terkait dengan id_booking_treatment yang dipilih
            if ($detail && $detail->id_booking_treatment != $validated['id_booking_treatment']) {
                return response()->json([
                    'message' => 'Detail booking treatment tidak terkait dengan booking treatment yang dipilih',
                ], 422);
            }

            // Simpan gambar komplain jika ada
            $gambarPaths = [];
            if ($request->hasFile('gambar_komplain')) {
                foreach ($request->file('gambar_komplain') as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = public_path('komplain_images');
    
                    if (!file_exists($filePath)) {
                        mkdir($filePath, 0755, true);
                    }
    
                    $file->move($filePath, $fileName);
                    $gambarPaths[] = 'komplain_images/' . $fileName;
                }
            }

            // Simpan data komplain ke dalam database
            $validated['gambar_komplain'] = json_encode($gambarPaths); // Menyimpan array gambar dalam format JSON

            // Simpan komplain
            $komplain = Komplain::create($validated);

            return response()->json([
                'message' => 'Komplain berhasil ditambahkan',
                'data' => $komplain,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi data gagal', 'errors' => $e->errors()], 422);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan komplain', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan yang tidak terduga', 'error' => $e->getMessage()], 500);
        }
    }



    // public function show($id)
    // {
    //     $komplain = Komplain::with(['kompensasi', 'user'])->findOrFail($id);
    //     return response()->json($komplain);
    // }

    public function update(Request $request, $id)
    {
        try {
            // 1) Validasi input: balasan wajib, kompensasi opsional
            $data = $request->validate([
                'balasan_komplain'            => 'required|string',
                'id_kompensasi'               => 'nullable|exists:tb_kompensasi,id_kompensasi',
                'kode_kompensasi'             => 'nullable|string|unique:tb_kompensasi_diberikan,kode_kompensasi',
                'tanggal_berakhir_kompensasi' => 'nullable|date',
            ]);

            // 2) Ambil komplain dan update balasan
            $komplain = Komplain::findOrFail($id);
            $komplain->balasan_komplain = $data['balasan_komplain'];

            // 3) Hanya kalau ada id_kompensasi kita juga ubah status pemberian kompensasi
            if (! empty($data['id_kompensasi'])) {
                $komplain->pemberian_kompensasi = 'Sudah diberikan';
            }

            $komplain->save();

            $createdKomp = null;
            // 4) Jika id_kompensasi di‐set, buat record kompensasi_diberikan
            if (! empty($data['id_kompensasi'])) {
                $createdKomp = KompensasiDiberikan::create([
                    'id_komplain'                => $komplain->id_komplain,
                    'id_kompensasi'              => $data['id_kompensasi'],
                    'kode_kompensasi'            => $data['kode_kompensasi'] ?? null,
                    'tanggal_berakhir_kompensasi' => $data['tanggal_berakhir_kompensasi'] ?? null,
                ]);
            }

            // 5) Kembalikan response
            return response()->json([
                'message'               => 'Komplain berhasil diperbarui',
                'komplain'              => $komplain,
                'kompensasi_diberikan'  => $createdKomp,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi data gagal',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function totalPendingBalasan()
    {
        // Komplain dengan balasan_komplain NULL atau string kosong
        $count = Komplain::whereNull('balasan_komplain')
            ->orWhere('balasan_komplain', '')
            ->count();

        return response()->json([
            'success'       => true,
            'total_pending' => $count,
        ]);
    }
}
