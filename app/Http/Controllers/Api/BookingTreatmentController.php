<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookingTreatment;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BookingTreatmentController extends Controller
{
    public function index()
    {
        try {
            return response()->json(BookingTreatment::all(), 200);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data booking treatment', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan yang tidak terduga', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_user' => 'required|exists:tb_user,id_user',
                'waktu_treatment' => 'required|date',
                'status_booking_treatment' => 'required|string|max:255',
            ]);

            $booking = BookingTreatment::create($validated);

            return response()->json([
                'message' => 'Booking treatment berhasil ditambahkan',
                'data' => $booking,
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi data gagal', 'errors' => $e->errors()], 422);
        } catch (UnauthorizedHttpException $e) {
            return response()->json(['message' => 'Akses tidak diizinkan', 'error' => $e->getMessage()], 401);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['message' => 'Akses ditolak', 'error' => $e->getMessage()], 403);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan booking treatment', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan yang tidak terduga', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $booking = BookingTreatment::find($id);

            if (!$booking) {
                return response()->json(['message' => 'Booking treatment tidak ditemukan'], 404);
            }

            return response()->json($booking, 200);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data booking treatment', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan yang tidak terduga', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $booking = BookingTreatment::find($id);

            if (!$booking) {
                return response()->json(['message' => 'Booking treatment tidak ditemukan'], 404);
            }

            $validated = $request->validate([
                'id_user' => 'exists:tb_user,id_user',
                'waktu_treatment' => 'date',
                'status_booking_treatment' => 'string|max:255',
            ]);

            $booking->update($validated);

            return response()->json([
                'message' => 'Booking treatment berhasil diperbarui',
                'data' => $booking,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi data gagal', 'errors' => $e->errors()], 422);
        } catch (UnauthorizedHttpException $e) {
            return response()->json(['message' => 'Akses tidak diizinkan', 'error' => $e->getMessage()], 401);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['message' => 'Akses ditolak', 'error' => $e->getMessage()], 403);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat memperbarui booking treatment', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan yang tidak terduga', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $booking = BookingTreatment::find($id);

            if (!$booking) {
                return response()->json(['message' => 'Booking treatment tidak ditemukan'], 404);
            }

            $booking->delete();

            return response()->json(['message' => 'Booking treatment berhasil dihapus'], 200);
        } catch (UnauthorizedHttpException $e) {
            return response()->json(['message' => 'Akses tidak diizinkan', 'error' => $e->getMessage()], 401);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['message' => 'Akses ditolak', 'error' => $e->getMessage()], 403);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat menghapus booking treatment', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan yang tidak terduga', 'error' => $e->getMessage()], 500);
        }
    }
}
