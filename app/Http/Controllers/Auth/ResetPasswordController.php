<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Models\User;

class ResetPasswordController extends Controller
{
    /**
     * Reset the given user's password (Original method - with form)
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password berhasil direset.'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kadaluarsa.'
            ], 400);
        }
    }

    /**
     * BARU: Direct password reset (otomatis ubah ke Password123)
     */
    public function resetDirect(Request $request, $token)
    {
        $email = $request->query('email');

        if (!$email) {
            return $this->showResetResult(false, 'Email tidak ditemukan dalam permintaan.');
        }

        // Verify token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$resetRecord) {
            return $this->showResetResult(false, 'Token reset tidak ditemukan atau sudah kadaluarsa.');
        }

        // Check if token is valid
        if (!Hash::check($token, $resetRecord->token)) {
            return $this->showResetResult(false, 'Token reset tidak valid.');
        }

        // Check if token is not expired (1 hour)
        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            // Delete expired token
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return $this->showResetResult(false, 'Token reset sudah kadaluarsa. Silakan minta reset password baru.');
        }

        // Find user
        $user = User::where('email', $email)->first();

        if (!$user) {
            return $this->showResetResult(false, 'User tidak ditemukan.');
        }

        try {
            // Reset password to Password123
            $newPassword = 'Password123';
            $user->update([
                'password' => Hash::make($newPassword)
            ]);

            // Delete the reset token
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            // Trigger password reset event
            event(new PasswordReset($user));

            return $this->showResetResult(true, 'Password berhasil direset!', $newPassword, $user->nama_user);

        } catch (\Exception $e) {
            return $this->showResetResult(false, 'Terjadi kesalahan saat mereset password. Silakan coba lagi.');
        }
    }

    /**
     * Show password reset result page
     */
    private function showResetResult($success, $message, $newPassword = null, $userName = null)
    {
        $html = "
        <!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Reset Password - Klinik Aesthetic</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: linear-gradient(135deg, #B4CBB4 0%, #a1b550ff 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                .container {
                    background: white;
                    border-radius: 20px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                    padding: 40px;
                    max-width: 500px;
                    width: 100%;
                    text-align: center;
                }
                .icon {
                    font-size: 60px;
                    margin-bottom: 20px;
                }
                .success-icon { color: #10b981; }
                .error-icon { color: #ef4444; }
                h1 {
                    color: #1f2937;
                    margin-bottom: 20px;
                    font-size: 28px;
                }
                .message {
                    color: #6b7280;
                    margin-bottom: 30px;
                    font-size: 16px;
                    line-height: 1.6;
                }
                .password-box {
                    background: #f0fdf4;
                    border: 2px solid #10b981;
                    border-radius: 12px;
                    padding: 20px;
                    margin: 30px 0;
                }
                .password-label {
                    color: #059669;
                    font-weight: 600;
                    font-size: 14px;
                    margin-bottom: 10px;
                }
                .password-value {
                    color: #1f2937;
                    font-size: 24px;
                    font-weight: bold;
                    font-family: 'Courier New', monospace;
                    background: white;
                    padding: 15px;
                    border-radius: 8px;
                    border: 1px solid #d1fae5;
                    margin-bottom: 15px;
                }
                .warning {
                    background: #fef3cd;
                    border: 1px solid #fbbf24;
                    border-radius: 8px;
                    padding: 15px;
                    margin: 20px 0;
                    color: #92400e;
                    font-size: 14px;
                }
                .button {
                    background: #B4CBB4;
                    color: white;
                    padding: 12px 30px;
                    border: none;
                    border-radius: 8px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                    text-decoration: none;
                    display: inline-block;
                    margin-top: 20px;
                    transition: background 0.3s ease;
                }
                .button:hover {
                    background: #a1b550ff;
                }
                .footer {
                    margin-top: 30px;
                    color: #9ca3af;
                    font-size: 12px;
                }
                .copy-button {
                    background: #6b7280;
                    color: white;
                    border: none;
                    padding: 8px 15px;
                    border-radius: 5px;
                    font-size: 12px;
                    cursor: pointer;
                    margin-left: 10px;
                }
                .copy-button:hover {
                    background: #4b5563;
                }
                @media (max-width: 600px) {
                    .container { padding: 20px; }
                    h1 { font-size: 24px; }
                    .password-value { font-size: 20px; }
                }
            </style>
        </head>
        <body>
            <div class='container'>";

        if ($success) {
            $html .= "
                <div class='icon success-icon'>✅</div>
                <h1>Password Berhasil Direset!</h1>
                <div class='message'>
                    Halo {$userName}, password Anda telah berhasil direset.
                </div>

                <div class='password-box'>
                    <div class='password-label'>Password Baru Anda:</div>
                    <div class='password-value' id='password'>{$newPassword}</div>
                    <button class='copy-button' onclick='copyPassword()'>📋 Salin</button>
                </div>

                <div class='warning'>
                    <strong>⚠️ PENTING:</strong><br>
                    Jangan lupa mengganti password akunmu setelah login!<br>
                    Gunakan password yang kuat untuk keamanan akun Anda.
                </div>";
        } else {
            $html .= "
                <div class='icon error-icon'>❌</div>
                <h1>Reset Password Gagal</h1>
                <div class='message'>{$message}</div>

                <div style='margin-top: 30px;'>
                    <a href='#' class='button' onclick='requestNewReset()'>
                        Minta Reset Password Baru
                    </a>
                </div>";
        }

        $html .= "
                <div class='footer'>
                    <p>&copy; 2025 Klinik Aesthetic. All rights reserved.</p>
                </div>
            </div>

            <script>
                function copyPassword() {
                    const passwordText = document.getElementById('password').textContent;
                    navigator.clipboard.writeText(passwordText).then(function() {
                        alert('Password berhasil disalin ke clipboard!');
                    });
                }

                function redirectToLogin() {
                    // Redirect ke halaman login frontend
                    window.location.href = 'https://yourfrontend.com/login';
                }

                function requestNewReset() {
                    // Redirect ke halaman forgot password frontend
                    window.location.href = 'https://yourfrontend.com/forgot-password';
                }
            </script>
        </body>
        </html>";

        return response($html, $success ? 200 : 400)->header('Content-Type', 'text/html');
    }
}
