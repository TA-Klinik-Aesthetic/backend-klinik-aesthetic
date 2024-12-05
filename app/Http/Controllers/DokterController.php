<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokter; 

class DokterController extends Controller
{
    public function index()
    {
        $dokters = Dokter::all(); // Mengambil semua data dokter
        return response()->json([
            'success' => true,
            'message' => 'Data Dokter',
            'data' => $dokters
        ]);
    }
}
