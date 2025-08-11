<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // Import ini
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable; // Tambahkan ini
use Laravel\Sanctum\HasApiTokens; // Tambahkan ini


class User extends Authenticatable implements MustVerifyEmail // Pastikan nama model User dan implement MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable; // Pastikan HasApiTokens, HasFactory, dan Notifiable ada di sini


    protected $table = 'tb_user'; // Nama tabel di database

    protected $primaryKey = 'id_user'; // Nama kolom primary key

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'nama_user',
        'no_telp',
        'email',
        'tanggal_lahir',
        'jenis_kelamin', // Mengganti 'foto_profil' dengan 'jenis_kelamin'
        'password',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime', // Pastikan ini ada untuk verifikasi email
        'tanggal_lahir' => 'date', // Cast tanggal_lahir sebagai date
        'password' => 'hashed', // Pastikan password di-hash otomatis saat diatur
    ];

    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail(): bool
    {
        // Hanya pelanggan yang perlu verifikasi email
        if ($this->role === 'pelanggan') {
            return ! is_null($this->email_verified_at);
        }
        // Untuk role lain, anggap email sudah terverifikasi secara default
        return true;
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->email;
    }
}
