<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\KonsultasiController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\DetailKonsultasiController;
use App\Http\Controllers\FeedbackController;



//authentikasi {
//endpoint untuk register

Route::post('/register', [AuthController::class, 'register']);

//endpoint untuk login
Route::post('/login', [AuthController::class, 'login']);

//endpoint untuk logout
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

//informasi tiap entitas{
// Route untuk mendapatkan semua pengguna
Route::get('/users', [UserController::class, 'index']);

// Route untuk mendapatkan semua dokter
Route::get('/dokters', [DokterController::class, 'index']);
//}

//konsultasi{
// Endpoint untuk menampilkan seluruh data konsultasi
Route::get('/konsultasi', [KonsultasiController::class, 'index']);

// Endpoint untuk menampilkan data konsultasi berdasarkan id
Route::get('/konsultasi/{id}', [KonsultasiController::class, 'show']);

// Endpoint untuk memasukkan data konsultasi seperti user dan waktu konsultasi
Route::post('/konsultasi', [KonsultasiController::class, 'store']);

// Endpoint untuk mengupdate informasi konsultasi (seperti memasukkan nama dokter)
Route::put('/konsultasi/{id_konsultasi}', [KonsultasiController::class, 'updateByKonsultasi']);

// Endpoint untuk menghubungkan konsultasi ke detail konsultasi
Route::post('/detail-konsultasi/connect', [DetailKonsultasiController::class, 'connect']);

// Endpoint untuk memperbarui atau mengisi detail konsultasi berdasarkan id_detail_konsultasi
Route::put('/detail-konsultasi/{id}', [DetailKonsultasiController::class, 'store']);

// Endpoint untuk menghubungkan konsultasi dengan feedback
Route::post('/feedback/connect', [FeedbackController::class, 'connect']);

// Endpoint untuk menyimpan atau memperbarui teks feedback
Route::post('/feedback/{id_konsultasi}/teks', [FeedbackController::class, 'storeTeksFeedback']);

// Endpoint untuk Menyimpan atau memperbarui balasan feedback
Route::post('/feedback/{id_konsultasi}/balasan', [FeedbackController::class, 'storeBalasanFeedback']);
//}

// Kategori Routes
Route::prefix('kategori')->group(function () {
    Route::get('/', [KategoriController::class, 'index']); // Get all categories
    Route::post('/', [KategoriController::class, 'store']); // Create a new category
    Route::get('/{id}', [KategoriController::class, 'show']); // Get a single category
    Route::put('/{id}', [KategoriController::class, 'update']); // Update a category
    Route::delete('/{id}', [KategoriController::class, 'destroy']); // Delete a category
});

// Produk Routes
Route::prefix('produk')->group(function () {
    Route::get('/', [ProdukController::class, 'index']); // Get all products
    Route::post('/', [ProdukController::class, 'store']); // Create a new product
    Route::get('/{id}', [ProdukController::class, 'show']); // Get a single product
    Route::put('/{id}', [ProdukController::class, 'update']); // Update a product
    Route::delete('/{id}', [ProdukController::class, 'destroy']); // Delete a product
});

