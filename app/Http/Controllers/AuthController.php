<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\FcmToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_user' => 'required|string|max:255',
            'no_telp' => 'required|string|unique:tb_user,no_telp',
            'email' => 'required|string|email|unique:tb_user,email',
            'password' => 'required|string|min:8|confirmed',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = User::create([
                'nama_user' => $request->nama_user,
                'no_telp' => $request->no_telp,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'role' => 'pelanggan',
            ]);

            // Kirim email verifikasi langsung untuk pelanggan
            if ($user->role === 'pelanggan') {
                $this->sendVerificationEmailViaMailtrap($user);
            }

            return response()->json([
                'message' => 'Registrasi berhasil. Silakan cek email Anda untuk verifikasi akun.',
                'status' => 'Menunggu Verifikasi',
                'user' => $user,
                'debug_info' => [
                    'email_sent_to' => $user->email,
                    'user_name' => $user->nama_user,
                    'verification_required' => true,
                    'mailtrap_note' => 'Email verifikasi akan muncul di Mailtrap inbox'
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error during registration', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat registrasi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login an existing user.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'device_type' => 'nullable|string|in:android,ios,web',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        // Cek verifikasi email untuk pelanggan
        if ($user->role === 'pelanggan' && !$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Akun Anda belum diverifikasi. Silakan cek email Anda.',
                'status' => 'unverified',
                'can_resend_verification' => true
            ], 403);
        }

        $token = $user->createToken('API Token')->plainTextToken;
        $fcmToken = $this->generateFcmToken($user->id_user, $request->device_type);

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token,
            'fcm_token' => $fcmToken,
        ], 200);
    }

    /**
     * Resend verification email
     */
    public function resendVerificationEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:tb_user,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Email tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email sudah diverifikasi sebelumnya'
            ], 400);
        }

        try {
            $this->sendVerificationEmailViaMailtrap($user);

            return response()->json([
                'message' => 'Email verifikasi berhasil dikirim ulang. Silakan cek inbox Anda.',
                'data' => [
                    'email_sent_to' => $user->email,
                    'user_name' => $user->nama_user,
                    'mailtrap_note' => 'Email akan muncul di Mailtrap inbox'
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error resending verification email', [
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Gagal mengirim email verifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Email verification handler
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::find($id);

        // Validasi user dan hash
        if (!$user || !hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return $this->showVerificationResult(false, 'Tautan verifikasi tidak valid atau kadaluarsa.', null);
        }

        // Jika sudah diverifikasi
        if ($user->hasVerifiedEmail()) {
            return $this->showVerificationResult(true, 'Email Anda sudah diverifikasi sebelumnya.', $user, true);
        }

        // Verifikasi email
        if ($user->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($user));
        }

        return $this->showVerificationResult(true, 'Email Anda berhasil diverifikasi!', $user, false);
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request, $id)
    {
        $data = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::findOrFail($id);
        $user->password = Hash::make($data['password']);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah',
        ]);
    }

    /**
     * Logout the user
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'fcm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Nonaktifkan FCM token
        if ($request->fcm_token) {
            FcmToken::where('id_user', $user->id_user)
                ->where('device_token', $request->fcm_token)
                ->update(['is_active' => false]);
        } else {
            FcmToken::where('id_user', $user->id_user)
                ->update(['is_active' => false]);
        }

        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ], 200);
    }

    /**
     * Send verification email via Mailtrap
     */
    private function sendVerificationEmailViaMailtrap($user)
    {
        try {
            // Generate verification URL
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(60),
                [
                    'id' => $user->getKey(),
                    'hash' => sha1($user->getEmailForVerification()),
                ]
            );

            Log::info('Preparing verification email for Mailtrap', [
                'email' => $user->email,
                'verification_url' => $verificationUrl,
                'user_name' => $user->nama_user
            ]);

            // Kirim email menggunakan Laravel Mail facade
            Mail::send([], [], function ($message) use ($user, $verificationUrl) {
                $message->to($user->email, $user->nama_user)
                        ->subject('Verifikasi Email Anda - NAVYA HUB')
                        ->from(config('mail.from.address'), config('mail.from.name'))
                        ->html($this->generateVerificationEmailTemplate($user, $verificationUrl));
            });

            Log::info('Verification email sent successfully via Mailtrap', [
                'email' => $user->email,
                'user_name' => $user->nama_user
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending verification email via Mailtrap', [
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Generate verification email template
     */
    private function generateVerificationEmailTemplate($user, $verificationUrl)
    {
        return "
        <!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Verifikasi Email - NAVYA HUB</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background-color: #f8fafc;
                }

                .email-container {
                    max-width: 600px;
                    margin: 20px auto;
                    background: white;
                    border-radius: 16px;
                    overflow: hidden;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                }

                .header {
                    background: linear-gradient(135deg, #B4CBB4 0%, #56651aff 100%);
                    color: white;
                    padding: 40px 30px;
                    text-align: center;
                    position: relative;
                }

                .header::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\"><circle cx=\"50\" cy=\"50\" r=\"3\" fill=\"rgba(255,255,255,0.1)\"/><circle cx=\"20\" cy=\"20\" r=\"2\" fill=\"rgba(255,255,255,0.1)\"/><circle cx=\"80\" cy=\"30\" r=\"2\" fill=\"rgba(255,255,255,0.1)\"/><circle cx=\"30\" cy=\"80\" r=\"1.5\" fill=\"rgba(255,255,255,0.1)\"/><circle cx=\"70\" cy=\"70\" r=\"1.5\" fill=\"rgba(255,255,255,0.1)\"/></svg>') repeat;
                    opacity: 0.3;
                }

                .header-content {
                    position: relative;
                    z-index: 1;
                }

                .header h1 {
                    font-size: 32px;
                    margin-bottom: 10px;
                    font-weight: 700;
                }

                .header p {
                    opacity: 0.95;
                    font-size: 18px;
                    font-weight: 300;
                }

                .verification-icon {
                    font-size: 60px;
                    margin-bottom: 15px;
                    display: block;
                }

                .content {
                    padding: 50px 40px;
                }

                .greeting {
                    font-size: 20px;
                    color: #2d3748;
                    margin-bottom: 25px;
                    font-weight: 600;
                }

                .message {
                    color: #4a5568;
                    margin-bottom: 35px;
                    font-size: 16px;
                    line-height: 1.8;
                }

                .welcome-box {
                    background: linear-gradient(135deg, #e6fffa 0%, #f0fff4 100%);
                    border: 2px solid #B4CBB4;
                    border-radius: 12px;
                    padding: 25px;
                    margin: 30px 0;
                    text-align: center;
                }

                .welcome-box h3 {
                    color: #B4CBB4;
                    margin-bottom: 15px;
                    font-size: 20px;
                }

                .welcome-box p {
                    color: #B4CBB4;
                    font-size: 16px;
                    line-height: 1.6;
                }

                .verify-button {
                    text-align: center;
                    margin: 40px 0;
                }

                .verify-button a {
                    display: inline-block;
                    background: linear-gradient(135deg, #B4CBB4 0%, #56651aff 100%);
                    color: white;
                    padding: 18px 50px;
                    text-decoration: none;
                    border-radius: 50px;
                    font-weight: 700;
                    font-size: 18px;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    transition: all 0.3s ease;
                    box-shadow: 0 8px 25px rgba(72, 187, 120, 0.3);
                    position: relative;
                    overflow: hidden;
                }

                .verify-button a::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: -100%;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
                    transition: left 0.5s;
                }

                .verify-button a:hover::before {
                    left: 100%;
                }

                .features {
                    background: #f7fafc;
                    border-radius: 12px;
                    padding: 30px;
                    margin: 30px 0;
                }

                .features h3 {
                    color: #2d3748;
                    margin-bottom: 20px;
                    font-size: 18px;
                    text-align: center;
                }

                .features-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                    gap: 20px;
                    margin-top: 20px;
                }

                .feature-item {
                    text-align: center;
                    padding: 15px;
                }

                .feature-icon {
                    font-size: 30px;
                    margin-bottom: 10px;
                    display: block;
                }

                .feature-text {
                    color: #4a5568;
                    font-size: 14px;
                    font-weight: 500;
                }

                .security-note {
                    background: #fff5f5;
                    border: 2px solid #fed7d7;
                    border-radius: 10px;
                    padding: 20px;
                    margin: 25px 0;
                    text-align: center;
                }

                .security-note strong {
                    color: #c53030;
                    font-size: 16px;
                }

                .security-note p {
                    color: #742a2a;
                    margin-top: 10px;
                    font-size: 14px;
                }

                .alternative-link {
                    background: #edf2f7;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 25px 0;
                    font-size: 12px;
                    color: #718096;
                    word-break: break-all;
                }

                .footer {
                    background: #2d3748;
                    color: #a0aec0;
                    padding: 40px 30px;
                    text-align: center;
                }

                .footer .brand {
                    color: #fff;
                    font-weight: 700;
                    font-size: 20px;
                    margin-bottom: 15px;
                }

                .footer .tagline {
                    color: #cbd5e0;
                    font-style: italic;
                    margin-bottom: 20px;
                }

                .footer p {
                    margin-bottom: 8px;
                    font-size: 14px;
                }

                @media (max-width: 600px) {
                    .email-container {
                        margin: 10px;
                        border-radius: 12px;
                    }

                    .content {
                        padding: 30px 25px;
                    }

                    .header {
                        padding: 30px 25px;
                    }

                    .header h1 {
                        font-size: 26px;
                    }

                    .verify-button a {
                        padding: 15px 35px;
                        font-size: 16px;
                    }

                    .features-grid {
                        grid-template-columns: 1fr;
                    }
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='header'>
                    <div class='header-content'>
                        <h1>Selamat Datang!</h1>
                        <h2>di NAVYA HUB</h2>
                        <p>Verifikasi Email Anda</p>
                    </div>
                </div>

                <div class='content'>
                    <div class='greeting'>
                        Halo <strong>{$user->nama_user}</strong>! 👋
                    </div>

                    <div class='message'>
                        Terima kasih telah mendaftar di <strong>NAVYA HUB</strong>!
                        Kami senang Anda bergabung dengan keluarga besar kami.
                        Untuk melengkapi proses registrasi, mohon verifikasi alamat email Anda dengan mengklik tombol di bawah ini.
                    </div>

                    <div class='verify-button'>
                        <a href='{$verificationUrl}'>Verifikasi Email Saya</a>
                    </div>

                    <div class='features'>
                        <h3>🌟 Yang Bisa Anda Lakukan Setelah Verifikasi:</h3>
                        <div class='features-grid'>
                            <div class='feature-item'>
                                <span class='feature-icon'>💬</span>
                                <div class='feature-text'>Konsultasi Online dengan Dokter</div>
                            </div>
                            <div class='feature-item'>
                                <span class='feature-icon'>💆‍♀️</span>
                                <div class='feature-text'>Booking Treatment Eksklusif</div>
                            </div>
                            <div class='feature-item'>
                                <span class='feature-icon'>🛍️</span>
                                <div class='feature-text'>Belanja Produk Kecantikan</div>
                            </div>
                            <div class='feature-item'>
                                <span class='feature-icon'>🎁</span>
                                <div class='feature-text'>Promo & Diskon Khusus</div>
                            </div>
                        </div>
                    </div>

                    <div class='security-note'>
                        <strong>🔒 Keamanan Penting!</strong>
                        <p>Link verifikasi ini akan kadaluarsa dalam <strong>60 menit</strong> untuk menjaga keamanan akun Anda.</p>
                    </div>

                    <div class='alternative-link'>
                        <strong>Tidak bisa klik tombol di atas?</strong><br>
                        Salin dan tempel link berikut ke browser Anda:<br>
                        <a href='{$verificationUrl}' style='color: #667eea;'>{$verificationUrl}</a>
                    </div>
                </div>

                <div class='footer'>
                    <div class='brand'>NAVYA HUB</div>
                    <div class='tagline'>\"Your Beauty, Our Priority\"</div>

                    <p>Jl. Kecantikan No. 123, Jakarta Selatan</p>
                    <p>📞 (021) 123-4567 | 📧 info@klinikaesthetic.com</p>
                    <p style='margin-top: 15px; font-size: 12px; opacity: 0.8;'>
                        Email ini dikirim secara otomatis, mohon jangan dibalas.
                    </p>
                    <p style='font-size: 12px; opacity: 0.8;'>
                        &copy; 2025 NAVYA HUB. All rights reserved.
                    </p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Show verification result page
     */
    private function showVerificationResult($success, $message, $user = null, $alreadyVerified = false)
    {
        $html = "
        <!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>" . ($success ? 'Verifikasi Berhasil' : 'Verifikasi Gagal') . " - NAVYA HUB</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: linear-gradient(135deg, #B4CBB4 0%, #a1b550ff 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                    position: relative;
                    overflow-x: hidden;
                }

                body::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\"><circle cx=\"20\" cy=\"20\" r=\"2\" fill=\"rgba(255,255,255,0.1)\"/><circle cx=\"80\" cy=\"20\" r=\"3\" fill=\"rgba(255,255,255,0.1)\"/><circle cx=\"20\" cy=\"80\" r=\"2\" fill=\"rgba(255,255,255,0.1)\"/><circle cx=\"80\" cy=\"80\" r=\"3\" fill=\"rgba(255,255,255,0.1)\"/><circle cx=\"50\" cy=\"50\" r=\"4\" fill=\"rgba(255,255,255,0.1)\"/></svg>') repeat;
                    animation: float 20s ease-in-out infinite;
                }

                @keyframes float {
                    0%, 100% { transform: translateY(0px); }
                    50% { transform: translateY(-20px); }
                }

                .container {
                    background: white;
                    border-radius: 25px;
                    box-shadow: 0 25px 50px rgba(0,0,0,0.2);
                    padding: 50px 40px;
                    max-width: 600px;
                    width: 100%;
                    text-align: center;
                    position: relative;
                    z-index: 1;
                    animation: slideUp 0.6s ease-out;
                }

                @keyframes slideUp {
                    from {
                        opacity: 0;
                        transform: translateY(30px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                .icon {
                    font-size: 80px;
                    margin-bottom: 25px;
                    animation: bounce 1s ease-in-out;
                }

                @keyframes bounce {
                    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
                    40% { transform: translateY(-10px); }
                    60% { transform: translateY(-5px); }
                }

                .success-icon {
                    color: #48bb78;
                    filter: drop-shadow(0 0 20px rgba(72, 187, 120, 0.3));
                }

                .error-icon {
                    color: #f56565;
                    filter: drop-shadow(0 0 20px rgba(245, 101, 101, 0.3));
                }

                h1 {
                    color: #2d3748;
                    margin-bottom: 20px;
                    font-size: 32px;
                    font-weight: 700;
                }

                .message {
                    color: #4a5568;
                    margin-bottom: 35px;
                    font-size: 18px;
                    line-height: 1.6;
                }

                .user-welcome {
                    background: linear-gradient(135deg, #e6fffa 0%, #f0fff4 100%);
                    border: 2px solid #48bb78;
                    border-radius: 15px;
                    padding: 25px;
                    margin: 30px 0;
                    animation: glow 2s ease-in-out infinite alternate;
                }

                @keyframes glow {
                    from { box-shadow: 0 0 20px rgba(72, 187, 120, 0.2); }
                    to { box-shadow: 0 0 30px rgba(72, 187, 120, 0.4); }
                }

                .user-welcome h3 {
                    color: #2f855a;
                    margin-bottom: 15px;
                    font-size: 22px;
                }

                .user-welcome p {
                    color: #276749;
                    font-size: 16px;
                    line-height: 1.6;
                }

                .features-preview {
                    background: #f7fafc;
                    border-radius: 15px;
                    padding: 30px;
                    margin: 35px 0;
                }

                .features-preview h3 {
                    color: #2d3748;
                    margin-bottom: 25px;
                    font-size: 20px;
                }

                .features-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                    gap: 20px;
                    margin-top: 20px;
                }

                .feature-item {
                    background: white;
                    padding: 20px;
                    border-radius: 12px;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                    transition: transform 0.3s ease;
                }

                .feature-item:hover {
                    transform: translateY(-5px);
                }

                .feature-icon {
                    font-size: 35px;
                    margin-bottom: 10px;
                    display: block;
                }

                .feature-text {
                    color: #4a5568;
                    font-size: 14px;
                    font-weight: 600;
                }

                .action-buttons {
                    margin-top: 40px;
                }

                .button {
                    display: inline-block;
                    padding: 15px 35px;
                    margin: 10px;
                    border: none;
                    border-radius: 50px;
                    font-size: 16px;
                    font-weight: 600;
                    text-decoration: none;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    position: relative;
                    overflow: hidden;
                }

                .button::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: -100%;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
                    transition: left 0.5s;
                }

                .button:hover::before {
                    left: 100%;
                }

                .primary-button {
                    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
                    color: white;
                    box-shadow: 0 8px 25px rgba(72, 187, 120, 0.3);
                }

                .primary-button:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 12px 35px rgba(72, 187, 120, 0.4);
                }

                .secondary-button {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
                }

                .secondary-button:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
                }

                .error-actions {
                    background: #fff5f5;
                    border: 2px solid #fed7d7;
                    border-radius: 15px;
                    padding: 25px;
                    margin: 30px 0;
                }

                .error-actions h3 {
                    color: #c53030;
                    margin-bottom: 15px;
                    font-size: 18px;
                }

                .error-actions p {
                    color: #742a2a;
                    margin-bottom: 20px;
                }

                .footer {
                    margin-top: 40px;
                    color: #a0aec0;
                    font-size: 14px;
                }

                .footer .brand {
                    color: #2d3748;
                    font-weight: 700;
                    font-size: 18px;
                    margin-bottom: 10px;
                }

                @media (max-width: 600px) {
                    .container {
                        padding: 30px 25px;
                        border-radius: 20px;
                    }

                    h1 {
                        font-size: 26px;
                    }

                    .icon {
                        font-size: 60px;
                    }

                    .button {
                        padding: 12px 25px;
                        font-size: 14px;
                        margin: 5px;
                        display: block;
                        width: 100%;
                    }

                    .features-grid {
                        grid-template-columns: repeat(2, 1fr);
                        gap: 15px;
                    }
                }
            </style>
        </head>
        <body>
            <div class='container'>";

        if ($success) {
            $html .= "
                <h1>
                    " . ($alreadyVerified ? 'Email Sudah Terverifikasi' : 'Verifikasi Berhasil!') . "
                </h1>

                <div class='message'>
                    {$message}
                </div>";

            if ($user) {
                $html .= "
                <div class='user-welcome'>
                    <h3>🌟 Selamat Datang, {$user->nama_user}!</h3>";

                if (!$alreadyVerified) {
                    $html .= "
                        <p>
                            Akun Anda telah berhasil diverifikasi dan siap digunakan.
                            Nikmati semua layanan premium kami untuk pengalaman kecantikan terbaik!
                        </p>";
                } else {
                    $html .= "
                        <p>
                            Akun Anda sudah aktif dan siap digunakan.
                            Lanjutkan menikmati layanan premium kami!
                        </p>";
                }

                $html .= "
                </div>";
            }

            $html .= "
                <div class='features-preview'>
                    <h3>🚀 Sekarang Anda Dapat:</h3>
                    <div class='features-grid'>
                        <div class='feature-item'>
                            <span class='feature-icon'>💬</span>
                            <div class='feature-text'>Konsultasi dengan Dokter</div>
                        </div>
                        <div class='feature-item'>
                            <span class='feature-icon'>💆‍♀️</span>
                            <div class='feature-text'>Booking Treatment</div>
                        </div>
                        <div class='feature-item'>
                            <span class='feature-icon'>🛍️</span>
                            <div class='feature-text'>Belanja Produk</div>
                        </div>
                        <div class='feature-item'>
                            <span class='feature-icon'>🎁</span>
                            <div class='feature-text'>Dapatkan Promo</div>
                        </div>
                    </div>
                </div>";

        } else {
            $html .= "
                <div class='icon error-icon'>❌</div>
                <h1>Verifikasi Gagal</h1>
                <div class='message'>{$message}</div>

                <div class='error-actions'>
                    <h3>💡 Apa yang harus dilakukan?</h3>
                    <p>Jangan khawatir! Ada beberapa cara untuk mengatasi masalah ini:</p>
                    <ul style='text-align: left; color: #742a2a; margin: 15px 0;'>
                        <li>Pastikan Anda mengklik link dari email terbaru</li>
                        <li>Cek folder spam/junk email Anda</li>
                        <li>Minta kirim ulang email verifikasi</li>
                        <li>Hubungi customer service jika masalah berlanjut</li>
                    </ul>
                </div>

                <div class='action-buttons'>
                    <a href='#' class='button primary-button' onclick='requestNewVerification()'>
                        📧 Kirim Ulang Email
                    </a>
                    <a href='#' class='button secondary-button' onclick='contactSupport()'>
                        🆘 Hubungi Support
                    </a>
                </div>";
        }

        $html .= "
                <div class='footer'>
                    <div class='brand'>NAVYA HUB</div>
                    <p>&copy; 2025 NAVYA HUB. All rights reserved.</p>
                    <p>Jl. Kecantikan No. 123, Jakarta Selatan</p>
                </div>
            </div>

            <script>
                function redirectToApp() {
                    window.location.href = 'https://yourapp.com/dashboard';
                }

                function exploreServices() {
                    window.location.href = 'https://yourapp.com/services';
                }

                function requestNewVerification() {
                    window.location.href = 'https://yourapp.com/resend-verification';
                }

                function contactSupport() {
                    window.location.href = 'https://yourapp.com/contact';
                }";

        $html .= "
            </script>
        </body>
        </html>";

        return response($html, $success ? 200 : 400)->header('Content-Type', 'text/html');
    }

    /**
     * Generate FCM Token otomatis oleh sistem
     */
    private function generateFcmToken($userId, $deviceType = null)
    {
        $timestamp = time();
        $randomString = bin2hex(random_bytes(16));
        $generatedToken = base64_encode("fcm_{$userId}_{$timestamp}_{$randomString}");

        FcmToken::create([
            'id_user' => $userId,
            'device_token' => $generatedToken,
            'device_type' => $deviceType ?? 'web',
            'is_active' => true
        ]);

        return $generatedToken;
    }
}
