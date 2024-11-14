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
        Schema::create('tb_dokter', function (Blueprint $table) {
            $table->increments('id'); // Menggunakan tipe int untuk id
            $table->string('nama_dokter',50);
            $table->string('no_telp',50);
            $table->string('email_dokter',50);
            $table->string('password');
            $table->string('nomor_izin_praktik',50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_dokter');
    }
};
