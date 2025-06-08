<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Komplain;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use App\Models\DetailBookingTreatment;
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
                    $path = $file->storeAs('komplain_images', $fileName, 'public');
                    if ($path) {
                        $gambarPaths[] = $path;
                    }
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
            // Validasi input balasan komplain dan tanggal berakhir kompensasi
            $data = $request->validate([
                'balasan_komplain' => 'required|string',
                'id_kompensasi' => 'nullable|exists:tb_kompensasi,id_kompensasi', // Menentukan kompensasi yang akan diberikan
                'kode_kompensasi' => 'nullable|string|unique:tb_kompensasi_diberikan,kode_kompensasi', // Menambahkan validasi untuk kode kompensasi
                'tanggal_berakhir_kompensasi' => 'nullable|date', // Validasi tanggal berakhir kompensasi
            ]);

            // Ambil data komplain berdasarkan ID
            $komplain = Komplain::findOrFail($id);

            // Update data komplain dengan balasan
            $komplain->balasan_komplain = $data['balasan_komplain'];
            $komplain->pemberian_kompensasi = 'Sudah dikirim'; // Menandakan bahwa kompensasi sudah dikirim
            $komplain->save();

            // Menyimpan kompensasi yang diberikan bersama dengan balasan komplain
            $kompensasiDiberikan = KompensasiDiberikan::create([
                'id_komplain' => $komplain->id_komplain,
                'id_kompensasi' => $data['id_kompensasi'],
                'kode_kompensasi' => $data['kode_kompensasi'], // Membuat kode kompensasi secara otomatis
                'tanggal_berakhir_kompensasi' => $data['tanggal_berakhir_kompensasi'], // Menggunakan tanggal yang dikirimkan
            ]);

            // Mengembalikan respon sukses
            return response()->json([
                'message' => 'Komplain berhasil diperbarui dan kompensasi berhasil dikirim',
                'komplain' => $komplain,
                'kompensasi_diberikan' => $kompensasiDiberikan,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan', 'error' => $e->getMessage()], 500);
        }
    }
}
