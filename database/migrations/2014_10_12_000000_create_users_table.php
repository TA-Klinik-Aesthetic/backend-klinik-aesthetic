<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tb_user', function (Blueprint $table) {
            $table->increments('id_user'); // Menggunakan tipe int untuk id
            $table->string('nama_user', 50);
            $table->string('no_telp', 15)->unique();
            $table->string('email', 100)->unique();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable();
            $table->string('password', 100);
            $table->enum('role', ['pelanggan', 'front office', 'kasir'])->default('pelanggan');
            $table->timestamp('email_verified_at')->nullable();
            // $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_user');
    }
};
