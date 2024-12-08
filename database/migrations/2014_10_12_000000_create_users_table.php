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
            $table->string('nama_user');
            $table->string('no_telp')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['pelanggan', 'dokter', 'beautician', 'front office', 'kasir', 'admin'])->default('pelanggan');
            $table->rememberToken();
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
