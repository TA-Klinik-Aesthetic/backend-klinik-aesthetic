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
use App\Http\Controllers\BeauticianController;
use App\Http\Controllers\KonsultasiController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\DetailKonsultasiController;
use App\Http\Controllers\PembelianProdukController;
use App\Http\Controllers\Api\TreatmentController;
use App\Http\Controllers\Api\JenisTreatmentController;
use App\Http\Controllers\Api\BookingTreatmentController;
use App\Http\Controllers\Api\DetailBookingTreatmentController;
use App\Http\Controllers\Api\FeedbackControllerKonsultasi;
use App\Http\Controllers\Api\FeedbackTreatmentApiController;
use App\Http\Controllers\JadwalPraktikBeauticianController;
use App\Http\Controllers\JadwalPraktikDokterController;
use App\Http\Controllers\PromoController;


//authentikasi {
//endpoint untuk register

Route::post('/register', [AuthController::class, 'register']);

//endpoint untuk login
Route::post('/login', [AuthController::class, 'login']);

// Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    //informasi tiap entitas{
    // Route untuk mendapatkan semua pengguna
    Route::get('/users', [UserController::class, 'index']);

    // Route untuk mendapatkan informasi pengguna yang sedang login
    // Route::get('/user/me', [UserController::class, 'me']);

    // Route untuk mendapatkan semua dokter
    Route::get('/dokters', [DokterController::class, 'index']);
    //}

    // Route untuk mendapatkan semua dokter
    Route::get('/beauticians', [BeauticianController::class, 'index']);
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

    //Endpoint untuk hanya melihat detail dari konsultasi berdasarkan id
    Route::get('/detail-konsultasi/{id}', [KonsultasiController::class, 'showDetail']);

    // Endpoint untuk memperbarui atau mengisi detail konsultasi berdasarkan id_detail_konsultasi
    Route::put('/detail-konsultasi/{id}', [DetailKonsultasiController::class, 'store']);

    // Route untuk delete konsultasi
    Route::delete('/konsultasi/{id}', [KonsultasiController::class, 'destroy']);
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
        Route::get('/kategori/{id_kategori}', [ProdukController::class, 'getProdukByKategori']);
    });

    // Products Purchase Management
    Route::prefix('products-purchase')->group(function () {
        Route::post('/pembelian', [PembelianProdukController::class, 'store']); // Create new purchase
        Route::get('/pembelian', [PembelianProdukController::class, 'index']); // Get all purchases
        Route::get('/pembelian/{id}', [PembelianProdukController::class, 'show']); // Get purchase details by ID
        Route::put('/pembelian/{id}', [PembelianProdukController::class, 'update']); // Edit the tb_pembelian purchase
        Route::delete('/pembelian/{id}', [PembelianProdukController::class, 'destroy']); // Delete purchase
    });

    Route::prefix('promos')->group(function () {
        Route::get('/', [PromoController::class, 'index']); // Menampilkan semua promo
        Route::post('/', [PromoController::class, 'store']); // Menambahkan promo baru
        Route::get('/{id}', [PromoController::class, 'show']); // Menampilkan detail promo berdasarkan ID
        Route::put('/{id}', [PromoController::class, 'update']); // Memperbarui promo berdasarkan ID
        Route::delete('/{id}', [PromoController::class, 'destroy']); // Menghapus promo berdasarkan ID
    });

    //ALL about TREATMENTSSSSSSS
    Route::prefix('treatments')->group(function () {
        Route::apiResource('/', TreatmentController::class)
            ->parameters(['' => 'treatment']);
    });

    Route::prefix('jenisTreatments')->group(function () {
        Route::apiResource('/', JenisTreatmentController::class)
            ->parameters(['' => 'jenisTreatment']);
    });

    Route::prefix('bookingTreatments')->group(function () {
        Route::apiResource('/', BookingTreatmentController::class)
            ->parameters(['' => 'bookingTreatment']);
    });

    Route::prefix('detailBookingTreatments')->group(function () {
        Route::apiResource('/', DetailBookingTreatmentController::class)
            ->parameters(['' => 'detailBookingTreatment']);
    });


    //ALL ABOUTTT FEEDBACKK
    Route::prefix('feedbacks')->group(function () {
        Route::apiResource('/', FeedbackControllerKonsultasi::class)
            ->parameters(['' => 'feedback']);
    });

    Route::prefix('feedbackTreatments')->group(function () {
        Route::apiResource('/', FeedbackTreatmentApiController::class)
            ->parameters(['' => 'feedbackTreatment']);
    });

    Route::prefix('jadwal-dokter')->group(function () {
        Route::get('/', [JadwalPraktikDokterController::class, 'index']); // Get all categories
        Route::post('/', [JadwalPraktikDokterController::class, 'store']); // Create a new category
        Route::put('/{id}', [JadwalPraktikDokterController::class, 'update']); // Update a category
        Route::delete('/{id}', [JadwalPraktikDokterController::class, 'destroy']); // Delete a category
    });

    Route::prefix('jadwal-beautician')->group(function () {
        Route::get('/', [JadwalPraktikBeauticianController::class, 'index']); // Get all categories
        Route::post('/', [JadwalPraktikBeauticianController::class, 'store']); // Create a new category
        Route::put('/{id}', [JadwalPraktikBeauticianController::class, 'update']); // Update a category
        Route::delete('/{id}', [JadwalPraktikBeauticianController::class, 'destroy']); // Delete a category
    });
// });
