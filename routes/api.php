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
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
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
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PembayaranMidtransController;
use App\Http\Controllers\RekamMedisController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PaketTreatmentController;
use App\Http\Controllers\PenjualanPaketTreatmentController;
use App\Http\Controllers\PaketTreatmentPelangganController;
use App\Http\Controllers\BookingTreatmentPaketController;
use App\Http\Controllers\InventarisStokController;
use App\Http\Controllers\DetailPembelianProdukController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FcmTokenController;
use App\Http\Controllers\NotifikasiController;

// Authentikasi Pelanggan
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// update password khusus front office
Route::put('user/{id}/password',[AuthController::class, 'updatePassword']); // tanpa s

// Forgot Password Routes - UPDATE
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.reset');

// BARU: Direct reset password route
Route::get('/password/reset-direct/{token}', [ResetPasswordController::class, 'resetDirect'])->name('password.reset.direct');

// Email verification routes
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');

Route::post('/email/resend-verification', [AuthController::class, 'resendVerificationEmail']);

// Routes that require authentication (using Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Logout route with auth:sanctum middleware
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']); // Use PUT for updates

    // Routes that can only be accessed by verified customers
    Route::middleware([EnsureEmailIsVerified::class])->group(function () {
        Route::get('/pelanggan/dashboard', function (Request $request) {
            // Logic for the verified customer dashboard
            return response()->json(['message' => 'Selamat datang di dashboard pelanggan Anda yang terverifikasi!'], 200);
        });
        // Add other routes here that are only for verified customers
    });

    // Example route that can be accessed by all logged-in users (including non-customers or unverified customers)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

//informasi tiap entitas{
Route::get('/user', [UserController::class, 'index']); // tanpa s
Route::get('/user/{id}', [UserController::class, 'show']); // tanpa s
Route::put('/user/{id}', [UserController::class, 'update']); // tanpa s

// Route untuk mendapatkan informasi pengguna yang sedang login
// Route::get('/user/me', [UserController::class, 'me']);

// Route untuk mendapatkan semua dokter
Route::get('/dokter', [DokterController::class, 'index']); // tanpa s
//}

// Route untuk mendapatkan semua dokter
Route::get('/beautician', [BeauticianController::class, 'index']); // tanpa s
//}


//konsultasi{
// Endpoint untuk menampilkan seluruh data konsultasi
Route::get('/konsultasi', [KonsultasiController::class, 'index']);

Route::get('konsultasi/total-verifikasi', [KonsultasiController::class, 'totalVerifikasi']);

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

// Route untuk update keluhan pelanggan
Route::put('/konsultasi/{id_konsultasi}/keluhan', [KonsultasiController::class, 'updateKeluhan']);

Route::get('/konsultasi/user/{id_user}', [KonsultasiController::class, 'getByUser']);



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

Route::get('/top-produk',   [ProdukController::class,   'topProducts']);

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


Route::put('penjualan-produk/{id}/status-pengambilan', [PembelianProdukController::class, 'updateStatusPengambilan']);

// Products Purchase Management
Route::prefix('penjualan-produk')->group(function () {
    Route::post('/', [PembelianProdukController::class, 'store']); // Create new purchase
    Route::post('/kasir', [PembelianProdukController::class, 'storeKasir']);
    Route::get('/', [PembelianProdukController::class, 'index']); // Get all purchases
    Route::get('/{id}', [PembelianProdukController::class, 'show']); // Get purchase details by ID
    Route::get('/user/{id_user}', [PembelianProdukController::class, 'getByUser']); // Get purchases by user ID
    Route::put('/{id}', [PembelianProdukController::class, 'update']); // Edit the tb_pembelian purchase
    Route::delete('/{id}', [PembelianProdukController::class, 'destroy']); // Delete purchase
});

Route::delete('/detail-penjualan-produk/{id}', [DetailPembelianProdukController::class, 'destroy']);

// Routes untuk Favorit
Route::prefix('favorites')->group(function () {
    // Get All Favorites
    Route::get('/user/{userId}', [FavoriteController::class, 'getUserFavorites']);

    // Get Specific Type Favorites
    Route::get('/user/{userId}/doctors', [FavoriteController::class, 'getFavoriteDoctors']);
    Route::get('/user/{userId}/products', [FavoriteController::class, 'getFavoriteProducts']);
    Route::get('/user/{userId}/treatments', [FavoriteController::class, 'getFavoriteTreatments']);
});

// Toggle Favorite Routes
Route::post('/doctors/toggle-favorite', [DokterController::class, 'toggleFavorite']);
Route::post('/products/toggle-favorite', [ProdukController::class, 'toggleFavorite']);
Route::post('/treatments/toggle-favorite', [TreatmentController::class, 'toggleFavorite']);

//ALL about TREATMENTSSSSSSS
Route::prefix('treatment')->group(function () { // tanpa s
    Route::apiResource('/', TreatmentController::class)
        ->parameters(['' => 'treatment']);
});

Route::get('/top-treatment', [TreatmentController::class, 'topTreatments']);

Route::prefix('jenisTreatment')->group(function () { // tanpa s
    Route::apiResource('/', JenisTreatmentController::class)
        ->parameters(['' => 'jenisTreatment']);
});

Route::get('bookingTreatment/total-verifikasi', [DetailBookingTreatmentController::class, 'totalVerifikasi']); // tanpa s
Route::get('/bookingTreatment/user/{id_user}', [DetailBookingTreatmentController::class, 'getByUser']); // tanpa s


Route::prefix('bookingTreatment')->group(function () { // tanpa s
    Route::apiResource('/', DetailBookingTreatmentController::class)
        ->parameters(['' => 'bookingTreatments']);
});

Route::put('statusBookingTreatment/{bookingTreatment}', [DetailBookingTreatmentController::class, 'updateStatusBooking']); // tanpa s

// Paket Treatment
Route::get('/paket-treatment', [PaketTreatmentController::class, 'index']);
Route::get('/paket-treatment/{id}', [PaketTreatmentController::class, 'show']); 
Route::post('/paket-treatment', [PaketTreatmentController::class, 'store']);
Route::put('/paket-treatment/{id}', [PaketTreatmentController::class, 'update']);

// Penjualan Paket Treatment
Route::get('/penjualan-paket-treatment', [PenjualanPaketTreatmentController::class, 'index']);
Route::get('/penjualan-paket-treatment/{id}', [PenjualanPaketTreatmentController::class, 'show']);
Route::post('/penjualan-paket-treatment', [PenjualanPaketTreatmentController::class, 'store']);

// Paket Treatment
Route::get('/paket-treatment-pelanggan', [PaketTreatmentPelangganController::class, 'index']);
Route::get('/paket-treatment-pelanggan/{id}', [PaketTreatmentPelangganController::class, 'show']); 

//booking paket treatment
Route::prefix('booking-treatment-paket')->group(function () {
    Route::get('/',          [BookingTreatmentPaketController::class, 'index']);
    Route::get('{id}',       [BookingTreatmentPaketController::class, 'show']);
    Route::post('/',         [BookingTreatmentPaketController::class, 'store']);
    Route::put('{id}',       [BookingTreatmentPaketController::class, 'update']);           // update dokter/beautician
    Route::put('{id}/status',[BookingTreatmentPaketController::class, 'updateStatus']);     // update status
});

// Route::get('/detail-booking-treatment', [DetailBookingTreatmentController::class, 'indexDetail']);

// Route::get('/detail-booking-produk/{id_detail_booking_treatment}', [DetailBookingTreatmentController::class, 'showDetailBookingProduk']);


//ALL ABOUTTT FEEDBACKK
Route::prefix('feedbackKonsultasi')->group(function () { // tanpa s dan ada tambahan nama konsultasi
    Route::apiResource('/', FeedbackControllerKonsultasi::class)
        ->parameters(['' => 'feedbackKonsultasi']);
});

Route::prefix('feedbackTreatment')->group(function () { // tanpa s
    Route::apiResource('/', FeedbackTreatmentApiController::class)
        ->parameters(['' => 'feedbackTreatment']);
});

// Route::prefix('jadwal-dokter')->group(function () {
//     Route::get('/', [JadwalPraktikDokterController::class, 'index']); // Get all categories
//     Route::post('/', [JadwalPraktikDokterController::class, 'store']); // Create a new category
//     Route::put('/{id}', [JadwalPraktikDokterController::class, 'update']); // Update a category
//     Route::delete('/{id}', [JadwalPraktikDokterController::class, 'destroy']); // Delete a category
// });

// Route::prefix('jadwal-beautician')->group(function () {
//     Route::get('/', [JadwalPraktikBeauticianController::class, 'index']); // Get all categories
//     Route::post('/', [JadwalPraktikBeauticianController::class, 'store']); // Create a new category
//     Route::put('/{id}', [JadwalPraktikBeauticianController::class, 'update']); // Update a category
//     Route::delete('/{id}', [JadwalPraktikBeauticianController::class, 'destroy']); // Delete a category
// });

Route::get('promo', [PromoController::class, 'index']);
Route::get('/promo/{id}', [PromoController::class, 'show']);
Route::post('promo', [PromoController::class, 'store']);
Route::put('promo/{id}', [PromoController::class, 'update']);
Route::delete('promo/{id}', [PromoController::class, 'destroy']);


Route::get('/kompensasi', [KompensasiController::class, 'index']);
Route::post('/kompensasi', [KompensasiController::class, 'store']);
Route::put('/kompensasi/{id}', [KompensasiController::class, 'update']);

Route::get('/komplain/user/{id}', [KomplainController::class, 'getByUser']);
Route::get('komplain/total-pending', [KomplainController::class, 'totalPendingBalasan']);
Route::get('/komplain', [KomplainController::class, 'index']);
Route::post('/komplain', [KomplainController::class, 'store']);
Route::put('/komplain/{id}', [KomplainController::class, 'update']);
Route::get('/komplain/{id}', [KomplainController::class, 'show']);

Route::get('/kompensasi-diberikan', [KompensasiDiberikanController::class, 'index']);
Route::get('/kompensasi-diberikan/{id}', [KompensasiDiberikanController::class, 'show']);
Route::get('/kompensasi-diberikan/user/{id_user}', [KompensasiDiberikanController::class, 'getByUser']);
Route::post('/kompensasi-diberikan', [KompensasiDiberikanController::class, 'store']);
Route::put('/kompensasi-diberikan/{id}', [KompensasiDiberikanController::class, 'update']);

Route::get('/komplain-treatment', [KomplainTreatmentController::class, 'index']);

Route::get('pembayaran-treatment/total-bayar', [PembayaranController::class, 'totalBayarTreatment']);

Route::get('pembayaran-produk/total-bayar', [PembayaranController::class, 'totalBayarProduk']);

// Treatment payment routes
Route::put('/pembayaran-treatment/{id}/konfirmasi', [PembayaranController::class, 'confirmPaymentTreatment']);
Route::post('/pembayaran-treatment/create', [PembayaranController::class, 'createTreatment']);
Route::get('/pembayaran-treatment', [PembayaranController::class, 'indexTreatment']);
Route::get('/pembayaran-treatment/{id}', [PembayaranController::class, 'showTreatment']);
Route::put('/pembayaran-treatment/{id}', [PembayaranController::class, 'updateTreatment']);
Route::get('/pembayaran-treatment/total-bayar', [PembayaranController::class, 'totalBayarTreatment']);

// Product payment routes
Route::put('/pembayaran-produk/{id}/konfirmasi', [PembayaranController::class, 'confirmPayment']);
Route::get('/pembayaran-produk', [PembayaranController::class, 'indexProduk']);
Route::get('/pembayaran-produk/{id}', [PembayaranController::class, 'showProduk']);
Route::post('/pembayaran-produk', [PembayaranController::class, 'storeProduk']);
Route::put('/pembayaran-produk/{id}', [PembayaranController::class, 'updateProduk']);
Route::get('/pembayaran-produk/total-bayar', [PembayaranController::class, 'totalBayarProduk']);
Route::put('/pembayaran-produk/{id}/metode',[PembayaranController::class, 'updateMetodePembayaranProduk']);

// Paket Treatment Payment Routes
Route::get('/pembayaran-paket-treatment', [PembayaranController::class, 'indexPaketTreatment']);
Route::get('/pembayaran-paket-treatment/{id}', [PembayaranController::class, 'showPaketTreatment']);
Route::post('/pembayaran-paket-treatment', [PembayaranController::class, 'storePaketTreatment']);
Route::put('/pembayaran-paket-treatment/{id}/konfirmasi', [PembayaranController::class, 'confirmPaymentPaket']);


// Midtrans Payment Routes - UPDATED
Route::prefix('midtrans')->group(function () {
    // Test endpoints
    Route::get('/info', [PembayaranMidtransController::class, 'getApiInfo']);
    Route::get('/status-mapping', [PembayaranMidtransController::class, 'debugStatusMapping']);

    // BARU: Auto-sync endpoint - tanpa auth untuk kemudahan testing
    Route::get('/check/{id}', [PembayaranMidtransController::class, 'checkPaymentWithSync']);

    // Debug endpoint - tanpa auth
    Route::get('/debug/{id}', [PembayaranMidtransController::class, 'debugPaymentDetail']);

    // Webhook - tidak perlu auth karena dari Midtrans
    Route::post('/notification', [PembayaranMidtransController::class, 'handleNotification']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        // Treatment Payment
        Route::post('/treatment', [PembayaranMidtransController::class, 'createTreatmentPayment']);

        // Product Payment
        Route::post('/product', [PembayaranMidtransController::class, 'createProductPayment']);

        // Check Payment Status (original method)
        Route::post('/status', [PembayaranMidtransController::class, 'checkPaymentStatus']);

        // Get Payment Detail (original method)
        Route::get('/detail/{id}', [PembayaranMidtransController::class, 'getPaymentDetail']);
    });
});

// Produk
Route::prefix('pembayaran-produk')->group(function(){
    Route::get('/',            [PembayaranController::class,'indexProduk']);
    Route::post('/',           [PembayaranController::class,'storeProduk']);
    Route::get('/{id}',        [PembayaranController::class,'showProduk']);
    Route::put('/{id}',        [PembayaranController::class,'updateProduk']);
});

// Rekam Medis
Route::get('/rekam-medis', [RekamMedisController::class, 'index']);
Route::get('/rekam-medis/{id_user}', [RekamMedisController::class, 'show']);

Route::get('/laporan-penjualan-treatment', [LaporanController::class, 'indexTreatment']);
Route::get('/laporan-treatment-hari', [LaporanController::class, 'laporanHarianTreatment']);
Route::get('/laporan-treatment-bulan', [LaporanController::class, 'laporanBulananTreatment']);

Route::get('/laporan-penjualan-produk', [LaporanController::class, 'indexProduk']);
Route::get('/laporan-produk-hari', [LaporanController::class, 'laporanHarianProduk']);
Route::get('/laporan-produk-bulan', [LaporanController::class, 'laporanBulananProduk']);

Route::get('/laporan-penjualan-paket-treatment',          [LaporanController::class, 'indexPaketTreatment']);
Route::get('/laporan-paket-treatment-hari',   [LaporanController::class, 'laporanHarianPaketTreatment']);
Route::get('/laporan-paket-treatment-bulan',  [LaporanController::class, 'laporanBulananPaketTreatment']);


// FCM Token Routes
Route::post('/fcm/register', [FcmTokenController::class, 'store']);
Route::post('/fcm/unregister', [FcmTokenController::class, 'destroy']);

// Notification Routes
Route::get('/notifications/{idUser}', [NotifikasiController::class, 'getUserNotifications']);
Route::post('/notifications/read/{id}', [NotifikasiController::class, 'markAsRead']);
Route::post('/notifications/read-all/{idUser}', [NotifikasiController::class, 'markAllAsRead']);
Route::post('/notifications/test', [NotifikasiController::class, 'sendTestNotification']);

Route::get('/debug/fcm', function() {
    return response()->json([
        'fcm_config' => [
            'server_key_exists' => !empty(config('services.fcm.server_key')),
            'server_key_length' => strlen(config('services.fcm.server_key') ?? ''),
        ],
        'models_exist' => [
            'FcmToken' => class_exists('App\\Models\\FcmToken'),
            'Notifikasi' => class_exists('App\\Models\\Notifikasi'),
        ],
        'database' => [
            'fcm_token_table' => \Illuminate\Support\Facades\Schema::hasTable('tb_fcm_token'),
            'notifikasi_table' => \Illuminate\Support\Facades\Schema::hasTable('tb_notifikasi'),
        ],
        'environment' => [
            'app_debug' => env('APP_DEBUG'),
            'php_version' => PHP_VERSION,
        ]
    ]);
});
