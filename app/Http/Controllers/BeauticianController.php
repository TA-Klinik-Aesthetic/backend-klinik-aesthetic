<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Beautician; 

class BeauticianController extends Controller
{
    public function index()
    {
        $beautician = Beautician::all(); // Mengambil semua data dokter
        return response()->json([
            'success' => true,
            'message' => 'Data Beautician',
            'data' => $beautician
        ]);
    }
}
