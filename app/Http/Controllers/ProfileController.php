<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // Import Rule untuk validasi enum
use App\Models\User; // Pastikan ini mengarah ke model User Anda

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile.
     */
    public function show(Request $request)
    {
        // $request->user() akan mengembalikan instance dari model User yang sedang login
        return response()->json([
            'user' => $request->user(),
            'message' => 'Data profil berhasil diambil.'
        ], 200);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(Request $request)
    {
        $user = $request->user(); // Dapatkan user yang sedang login

        $validator = Validator::make($request->all(), [
            'nama_user' => 'sometimes|string|max:255', // 'sometimes' berarti opsional
            'no_telp' => [
                'sometimes',
                'string',
                Rule::unique('tb_user')->ignore($user->id_user, 'id_user'), // Abaikan ID user saat ini
            ],
            // Email tidak boleh diubah melalui update profil biasa karena akan mengganggu verifikasi
            // 'email' => [
            //     'sometimes',
            //     'string',
            //     'email',
            //     Rule::unique('tb_user')->ignore($user->id_user, 'id_user'),
            // ],
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])],
            // Password diupdate di controller terpisah (ForgotPasswordController/ResetPasswordController)
            // 'foto_profil' => 'nullable|string', // Dihapus karena sudah diganti jenis_kelamin
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update data user
        $user->fill($request->only([
            'nama_user',
            'no_telp',
            'tanggal_lahir',
            'jenis_kelamin', // Tambahkan jenis_kelamin
            // 'foto_profil', // Dihapus
        ]));

        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => $user,
        ], 200);
    }
}
