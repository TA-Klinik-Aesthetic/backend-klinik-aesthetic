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
use App\Http\Controllers\KeranjangPembelianController;
use App\Http\Controllers\Api\TreatmentController;
use App\Http\Controllers\Api\JenisTreatmentController;
use App\Http\Controllers\Api\BookingTreatmentController;
use App\Http\Controllers\Api\DetailBookingTreatmentController;
use App\Http\Controllers\Api\FeedbackControllerKonsultasi;
use App\Http\Controllers\Api\FeedbackTreatmentApiController;
use App\Http\Controllers\JadwalPraktikBeauticianController;
use App\Http\Controllers\JadwalPraktikDokterController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\KompensasiController;
use App\Http\Controllers\KomplainController;
use App\Http\Controllers\KomplainTreatmentController;
use App\Http\Controllers\KompensasiDiberikanController;
use App\Http\Controllers\PembayaranTreatmentController;
use App\Http\Controllers\PembayaranProdukController;
use App\Http\Controllers\RekamMedisController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\InventarisStokController;


//authentikasi {
//endpoint untuk register

Route::post('/register', [AuthController::class, 'register']);

//endpoint untuk login
Route::post('/login', [AuthController::class, 'login']);

// Route::middleware('auth:sanctum')->group(function () {
// Logout
Route::post('/logout', [AuthController::class, 'logout']);

//informasi tiap entitas{
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}', [UserController::class, 'update']);

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
Route::put('/konsultasi/{id_konsultasi}', [KonsultasiController::class, 'updateDokter']);

// //Endpoint untuk hanya melihat detail dari konsultasi berdasarkan id
// Route::get('/detail-konsultasi/{id}', [KonsultasiController::class, 'showDetail']);

// Endpoint untuk memperbarui atau mengisi detail konsultasi berdasarkan id_detail_konsultasi
Route::post('/detail-konsultasi/{id}', [DetailKonsultasiController::class, 'store']);

// Route untuk delete konsultasi
Route::delete('/konsultasi/{id}', [KonsultasiController::class, 'destroy']);

// route untuk update status konsultasi
Route::put('/konsultasi/{id_konsultasi}', [KonsultasiController::class, 'updateStatus']);

//}

// Kategori Routes
Route::prefix('kategori')->group(function () {
    Route::get('/', [KategoriController::class, 'index']); // Get all categories
    Route::get('/all', [KeranjangPembelianController::class, 'getAll']); //To get all cart items
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

// Product Cart Management
Route::prefix('keranjang')->group(function () {
    Route::get('/', [KeranjangPembelianController::class, 'index']);
    Route::get('/user/{id_user}', [KeranjangPembelianController::class, 'getByUser']);
    Route::get('/user/{id_user}/total', [KeranjangPembelianController::class, 'getTotalProdukByUser']);
    Route::post('/', [KeranjangPembelianController::class, 'store']);
    Route::put('/{id}', [KeranjangPembelianController::class, 'update']);
    Route::delete('/{id}', [KeranjangPembelianController::class, 'destroy']);
    Route::delete('/user/{id_user}', [KeranjangPembelianController::class, 'destroyByUser']);
});

// Products Purchase Management
Route::prefix('penjualan-produk')->group(function () {
    Route::post('/', [PembelianProdukController::class, 'store']); // Create new purchase
    Route::get('/', [PembelianProdukController::class, 'index']); // Get all purchases
    Route::get('/{id}', [PembelianProdukController::class, 'show']); // Get purchase details by ID
    Route::put('/{id}', [PembelianProdukController::class, 'update']); // Edit the tb_pembelian purchase
    Route::delete('/{id}', [PembelianProdukController::class, 'destroy']); // Delete purchase
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

Route::put('statusBookingTreatments/{bookingTreatment}', [DetailBookingTreatmentController::class, 'updateStatusBooking']);
Route::get('/detail-booking-produk/{id_detail_booking_treatment}', [DetailBookingTreatmentController::class, 'showDetailBookingProduk']);


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

Route::get('promo', [PromoController::class, 'index']);
Route::get('/promo/{id}', [PromoController::class, 'show']);
Route::post('promo', [PromoController::class, 'store']);
Route::put('promo/{id}', [PromoController::class, 'update']);
Route::delete('promo/{id}', [PromoController::class, 'destroy']);


Route::get('/kompensasi', [KompensasiController::class, 'index']);
Route::post('/kompensasi', [KompensasiController::class, 'store']);
Route::put('/kompensasi/{id}', [KompensasiController::class, 'update']);

Route::get('/komplain', [KomplainController::class, 'index']);
Route::post('/komplain', [KomplainController::class, 'store']);
Route::put('/komplain/{id}', [KomplainController::class, 'update']);
Route::get('/komplain/{id}', [KomplainController::class, 'show']);

Route::get('/kompensasi-diberikan', [KompensasiDiberikanController::class, 'index']);
Route::get('/kompensasi-diberikan/{id}', [KompensasiDiberikanController::class, 'show']);
Route::post('/kompensasi-diberikan', [KompensasiDiberikanController::class, 'store']);
Route::put('/kompensasi-diberikan/{id}', [KompensasiDiberikanController::class, 'update']);

Route::get('/komplain-treatment', [KomplainTreatmentController::class, 'index']);


Route::get('/pembayaran-treatment', [PembayaranTreatmentController::class, 'index']);
Route::get('/pembayaran-treatment/{id}', [PembayaranTreatmentController::class, 'show']);
Route::post('/pembayaran-treatment', [PembayaranTreatmentController::class, 'store']);
Route::put('/pembayaran-treatment/{id}', [PembayaranTreatmentController::class, 'update']);

Route::resource('pembayaran-produk', PembayaranProdukController::class);

Route::get('/rekam-medis', [RekamMedisController::class, 'index']);
Route::get('/rekam-medis/{id_user}', [RekamMedisController::class, 'show']);

Route::get('/laporan-penjualan-treatment', [LaporanController::class, 'indexTreatment']);
Route::get('/laporan-treatment-hari', [LaporanController::class, 'laporanHarianTreatment']);
Route::get('/laporan-treatment-bulan', [LaporanController::class, 'laporanBulananTreatment']);

Route::get('/laporan-penjualan-produk', [LaporanController::class, 'indexProduk']);
Route::get('/laporan-produk-hari', [LaporanController::class, 'laporanHarianProduk']);
Route::get('/laporan-produk-bulan', [LaporanController::class, 'laporanBulananProduk']);


Route::get('/inventaris-stok', [InventarisStokController::class, 'index']);

// });
