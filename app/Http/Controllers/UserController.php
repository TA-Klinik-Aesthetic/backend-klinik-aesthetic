<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 

class UserController extends Controller
{
    public function index()
    {
        $users = User::all(); // Mengambil semua data pengguna
        return response()->json([
            'success' => true,
            'message' => 'Data Pengguna',
            'data' => $users
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Data Pengguna yang Sedang Login',
            'data' => $request->user(), // Data pengguna yang sedang login
        ]);
    }
}
