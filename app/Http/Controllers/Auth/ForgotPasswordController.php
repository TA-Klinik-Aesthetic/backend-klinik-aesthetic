<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    /**
     * Send reset link via Mailtrap
     */
    public function sendResetLinkEmail(Request $request)
    {
        Log::info('Password reset request received', [
            'email' => $request->email
        ]);

        // Validasi email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Format email tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cek apakah user ada
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Email tidak ditemukan dalam sistem kami.',
                'debug_info' => [
                    'searched_email' => $request->email,
                    'total_users' => User::count(),
                    'available_emails_sample' => User::pluck('email')->take(3)->toArray()
                ]
            ], 404);
        }

        try {
            // Generate reset token
            $token = Str::random(64);

            // Store reset token
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'email' => $request->email,
                    'token' => Hash::make($token),
                    'created_at' => now()
                ]
            );

            // Generate reset URL
            $resetUrl = url('/api/password/reset-direct/' . $token . '?email=' . urlencode($user->email));

            Log::info('Reset token created successfully', [
                'email' => $request->email,
                'user_id' => $user->id_user,
                'reset_url' => $resetUrl
            ]);

            // Kirim email melalui Mailtrap
            $this->sendResetEmailViaMailtrap($user, $resetUrl, $token);

            return response()->json([
                'message' => 'Link reset password telah dikirim ke email Anda. Silakan cek inbox di Mailtrap.',
                'data' => [
                    'email_sent_to' => $request->email,
                    'user_name' => $user->nama_user,
                    'expires_in' => '1 hour',
                    'mailtrap_note' => 'Email akan muncul di Mailtrap inbox untuk testing',
                    // DEVELOPMENT ONLY: Tampilkan link untuk testing
                    'development_reset_url' => $resetUrl
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error sending password reset email', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat mengirim email reset.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send reset email via Mailtrap
     */
    private function sendResetEmailViaMailtrap($user, $resetUrl, $token)
    {
        try {
            Log::info('Preparing reset email for Mailtrap', [
                'email' => $user->email,
                'reset_url' => $resetUrl,
                'user_name' => $user->nama_user
            ]);

            // Kirim email menggunakan Laravel Mail facade
            Mail::send([], [], function ($message) use ($user, $resetUrl, $token) {
                $message->to($user->email, $user->nama_user)
                        ->subject('Reset Password - Klinik Aesthetic')
                        ->from(config('mail.from.address'), config('mail.from.name'))
                        ->html($this->generateEmailTemplate($user, $resetUrl, $token));
            });

            Log::info('Reset email sent successfully via Mailtrap', [
                'email' => $user->email,
                'user_name' => $user->nama_user
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending reset email via Mailtrap', [
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Generate HTML email template
     */
    private function generateEmailTemplate($user, $resetUrl, $token)
    {
        return "
        <!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Reset Password - Klinik Aesthetic</title>
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
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }
                .header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 30px;
                    text-align: center;
                }
                .header h1 {
                    font-size: 28px;
                    margin-bottom: 10px;
                }
                .header p {
                    opacity: 0.9;
                    font-size: 16px;
                }
                .content {
                    padding: 40px 30px;
                }
                .greeting {
                    font-size: 18px;
                    color: #2d3748;
                    margin-bottom: 20px;
                }
                .message {
                    color: #4a5568;
                    margin-bottom: 30px;
                    font-size: 16px;
                    line-height: 1.7;
                }
                .warning-box {
                    background: #fef5e7;
                    border: 2px solid #f6ad55;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 25px 0;
                    text-align: center;
                }
                .warning-box strong {
                    color: #c05621;
                    font-size: 16px;
                }
                .warning-box p {
                    color: #744210;
                    margin-top: 10px;
                    font-size: 14px;
                }
                .reset-button {
                    text-align: center;
                    margin: 35px 0;
                }
                .reset-button a {
                    display: inline-block;
                    background: linear-gradient(135deg, #4c51bf 0%, #667eea 100%);
                    color: white;
                    padding: 15px 40px;
                    text-decoration: none;
                    border-radius: 50px;
                    font-weight: 600;
                    font-size: 16px;
                    transition: transform 0.2s ease;
                    box-shadow: 0 4px 15px rgba(76, 81, 191, 0.3);
                }
                .reset-button a:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(76, 81, 191, 0.4);
                }
                .password-info {
                    background: #f0fff4;
                    border: 2px solid #48bb78;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 25px 0;
                }
                .password-info h3 {
                    color: #2f855a;
                    margin-bottom: 15px;
                    font-size: 18px;
                }
                .password-info ul {
                    color: #276749;
                    padding-left: 20px;
                }
                .password-info li {
                    margin-bottom: 8px;
                }
                .security-note {
                    background: #edf2f7;
                    border: 1px solid #cbd5e0;
                    border-radius: 8px;
                    padding: 15px;
                    margin: 25px 0;
                    font-size: 14px;
                    color: #4a5568;
                }
                .debug-info {
                    background: #f7fafc;
                    border: 1px solid #e2e8f0;
                    border-radius: 6px;
                    padding: 15px;
                    margin: 20px 0;
                    font-size: 12px;
                    color: #718096;
                    font-family: 'Courier New', monospace;
                }
                .footer {
                    background: #2d3748;
                    color: #a0aec0;
                    padding: 30px;
                    text-align: center;
                    font-size: 14px;
                }
                .footer p {
                    margin-bottom: 10px;
                }
                .footer .brand {
                    color: #fff;
                    font-weight: 600;
                    font-size: 16px;
                }
                @media (max-width: 600px) {
                    .email-container {
                        margin: 10px;
                        border-radius: 8px;
                    }
                    .content {
                        padding: 25px 20px;
                    }
                    .header {
                        padding: 25px 20px;
                    }
                    .header h1 {
                        font-size: 24px;
                    }
                    .reset-button a {
                        padding: 12px 30px;
                        font-size: 14px;
                    }
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='header'>
                    <h1>🔐 Reset Password</h1>
                    <p>Permintaan reset password untuk akun Anda</p>
                </div>

                <div class='content'>
                    <div class='greeting'>
                        Halo <strong>{$user->nama_user}</strong>,
                    </div>

                    <div class='message'>
                        Kami menerima permintaan untuk mereset password akun Anda di <strong>Klinik Aesthetic</strong>.
                        Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini dan password Anda tidak akan berubah.
                    </div>

                    <div class='warning-box'>
                        <strong>⚠️ PENTING - BACA SEBELUM KLIK!</strong>
                        <p>Ketika Anda mengklik tombol di bawah ini, password akun Anda akan <strong>otomatis diubah menjadi: Password123</strong></p>
                    </div>

                    <div class='reset-button'>
                        <a href='{$resetUrl}'>RESET PASSWORD SAYA</a>
                    </div>

                    <div class='password-info'>
                        <h3>📋 Setelah Password Direset:</h3>
                        <ul>
                            <li><strong>Password baru Anda:</strong> Password123</li>
                            <li>Segera login dengan password baru tersebut</li>
                            <li><strong>WAJIB:</strong> Ganti password Anda sesegera mungkin untuk keamanan</li>
                            <li>Gunakan password yang kuat (minimal 8 karakter, kombinasi huruf, angka, dan simbol)</li>
                        </ul>
                    </div>

                    <div class='security-note'>
                        <strong>🔒 Keamanan:</strong> Link reset ini akan kadaluarsa dalam <strong>1 jam</strong>
                        dan hanya dapat digunakan sekali. Jika link sudah kadaluarsa, silakan minta reset password baru.
                    </div>

                    <div class='debug-info'>
                        <strong>Debug Info (Development):</strong><br>
                        Email: {$user->email}<br>
                        User ID: {$user->id_user}<br>
                        Token: {$token}<br>
                        Generated: " . now()->format('Y-m-d H:i:s') . "<br>
                        Expires: " . now()->addHour()->format('Y-m-d H:i:s') . "
                    </div>
                </div>

                <div class='footer'>
                    <div class='brand'>Klinik Aesthetic</div>
                    <p>Email ini dikirim secara otomatis, mohon jangan dibalas.</p>
                    <p>&copy; 2025 Klinik Aesthetic. All rights reserved.</p>
                    <p>Jika Anda mengalami masalah, hubungi support kami.</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
