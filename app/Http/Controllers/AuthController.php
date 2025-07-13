<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Pastikan ini mengarah ke model User Anda
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // Import Rule untuk validasi enum

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_user' => 'required|string|max:255',
            'no_telp' => 'required|string|unique:tb_user,no_telp', // Tambahkan unique
            'email' => 'required|string|email|unique:tb_user,email', // Tambahkan unique
            'password' => 'required|string|min:8|confirmed', // Tambahkan min:8 dan confirmed
            'tanggal_lahir' => 'nullable|date', // Tambahkan validasi tanggal lahir
            'jenis_kelamin' => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])], // Tambahkan validasi jenis kelamin dengan enum
            // Role secara default akan 'pelanggan' di sini, tidak perlu diinput dari request
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'nama_user' => $request->nama_user,
            'no_telp' => $request->no_telp,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'role' => 'pelanggan', // Set role default sebagai 'pelanggan'
            // email_verified_at akan null secara default di database
        ]);

        // Kirim email verifikasi hanya jika role adalah 'pelanggan'
        // Logika ini sudah ada di model User::sendEmailVerificationNotification()
        if ($user->role === 'pelanggan') {
            $user->sendEmailVerificationNotification();
        }

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil. Silakan cek email Anda untuk verifikasi akun.',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Login an existing user.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first(); // Gunakan User

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        // Opsional: Cek apakah email sudah diverifikasi jika role adalah 'pelanggan'
        // Jika Anda ingin memblokir login sebelum verifikasi, tambahkan ini:
        if ($user->role === 'pelanggan' && !$user->hasVerifiedEmail()) {
             return response()->json(['message' => 'Akun Anda belum diverifikasi. Silakan cek email Anda.'], 403);
        }


        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    /**
     * Logout the user (invalidate the token).
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ], 200);
    }
}
