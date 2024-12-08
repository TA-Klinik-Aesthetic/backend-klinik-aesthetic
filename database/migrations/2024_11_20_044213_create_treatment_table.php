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
        Schema::create('tb_jenis_treatment', function (Blueprint $table) {
            $table->increments('id_jenis_treatment'); // Menggunakan tipe int untuk id
            $table->string('nama_jenis_treatment', 50);
            $table->timestamps();
        });

        // Tabel tb_treatment
        Schema::create('tb_treatment', function (Blueprint $table) {
            $table->increments('id_treatment'); // Primary key
            $table->unsignedInteger('id_jenis_treatment'); // Foreign key ke tabel tb_jenis_treatment
            $table->string('nama_treatment');
            $table->string('deskripsi_treatment');
            $table->double('biaya_treatment', 15, 2); // Ubah menjadi tipe double
            $table->time('estimasi_treatment');
            $table->string('gambar_treatment');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_jenis_treatment')->references('id_jenis_treatment')->on('tb_jenis_treatment')->onDelete('cascade');
        });

        // Tabel tb_booking_treatment
        Schema::create('tb_booking_treatment', function (Blueprint $table) {
            $table->increments('id_booking_treatment'); // Primary key
            $table->unsignedInteger('id_user'); // Foreign key ke tabel tb_user
            $table->dateTime('waktu_treatment');
            $table->enum('status_booking_treatment', ['Verifikasi', 'Berhasil dibooking', 'Dibatalkan', 'Selesai'])->default('Verifikasi')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_user')->references('id_user')->on('tb_user')->onDelete('cascade');
        });

        // Tabel tb_booking_treatment
        Schema::create('tb_detail_booking_treatment', function (Blueprint $table) {
            $table->increments('id_detail_booking_treatment'); // Primary key
            $table->unsignedInteger('id_booking_treatment'); // Foreign key ke tabel tb_treatment
            $table->unsignedInteger('id_treatment'); // Foreign key ke tabel tb_treatment
            $table->double('harga_akhir_treatment', 15, 2)->nullable(); // Ubah menjadi tipe double
            $table->string('potongan_harga')->nullable(); // Ubah menjadi tipe double
            $table->unsignedInteger('id_dokter')->nullable(); // Relasi ke tabel tb_dokter
            $table->unsignedInteger('id_beautician')->nullable(); // Foreign key ke tabel tb_beautician
            $table->timestamps();
        
            // Foreign key constraints
            $table->foreign('id_booking_treatment')->references('id_booking_treatment')->on('tb_booking_treatment')->onDelete('cascade');
            $table->foreign('id_treatment')->references('id_treatment')->on('tb_treatment')->onDelete('cascade');
            $table->foreign('id_dokter')->references('id_dokter')->on('tb_dokter')->onDelete('cascade');
            $table->foreign('id_beautician')->references('id_beautician')->on('tb_beautician')->onDelete('cascade');
        
        });
    }
        
       
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_jenis_treatment');
        Schema::dropIfExists('tb_treatment');
        Schema::dropIfExists('tb_booking_treatment');
        Schema::dropIfExists('tb_detail_booking_treatment');
    }
};
