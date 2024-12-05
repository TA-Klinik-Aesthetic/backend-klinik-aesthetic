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
            $table->increments('id'); // Menggunakan tipe int untuk id
            $table->string('nama_jenis_treatment', 50);
            $table->timestamps();
        });

        // Tabel tb_treatment
        Schema::create('tb_treatment', function (Blueprint $table) {
            $table->increments('id'); // Primary key
            $table->unsignedInteger('id_jenis_treatment'); // Foreign key ke tabel tb_jenis_treatment
            $table->string('nama_treatment');
            $table->string('deskripsi_treatment');
            $table->double('biaya_treatment', 15, 2); // Ubah menjadi tipe double
            $table->string('estimasi_treatment');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_jenis_treatment')->references('id')->on('tb_jenis_treatment')->onDelete('cascade');
        });

        // Tabel tb_booking_treatment
        Schema::create('tb_booking_treatment', function (Blueprint $table) {
            $table->increments('id'); // Primary key
            $table->unsignedInteger('id_user')->nullable(); // Foreign key ke tabel tb_user
            $table->dateTime('waktu_treatment');
            $table->enum('status_booking_treatment', ['Berhasil dibooking', 'Dibatalkan', 'Selesai']);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_user')->references('id')->on('tb_user')->onDelete('cascade');
        });

        // Tabel tb_booking_treatment
        Schema::create('tb_detail_booking_treatment', function (Blueprint $table) {
            $table->increments('id'); // Primary key
            $table->unsignedInteger('id_booking'); // Foreign key ke tabel tb_treatment
            $table->unsignedInteger('id_treatment'); // Foreign key ke tabel tb_treatment
            $table->double('harga_treatment', 15, 2); // Ubah menjadi tipe double
            $table->unsignedInteger('id_dokter'); // Relasi ke tabel tb_dokter
            $table->unsignedInteger('id_beautician'); // Foreign key ke tabel tb_beautician
            $table->timestamps();
        
            // Foreign key constraints
            $table->foreign('id_booking')->references('id')->on('tb_booking_treatment')->onDelete('cascade');
            $table->foreign('id_treatment')->references('id')->on('tb_treatment')->onDelete('cascade');
            $table->foreign('id_dokter')->references('id')->on('tb_dokter')->onDelete('cascade');
            $table->foreign('id_beautician')->references('id')->on('tb_beautician')->onDelete('cascade');
        
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
