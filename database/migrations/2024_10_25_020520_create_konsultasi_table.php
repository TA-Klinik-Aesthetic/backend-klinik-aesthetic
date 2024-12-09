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
        // Tabel tb_konsultasi
        Schema::create('tb_konsultasi', function (Blueprint $table) {
            $table->increments('id_konsultasi'); // Primary key
            $table->unsignedInteger('id_user')->nullable(); // Foreign key ke tabel tb_user
            $table->unsignedInteger('id_dokter')->nullable(); // Foreign key ke tabel tb_dokter
            $table->dateTime('waktu_konsultasi');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_user')->references('id_user')->on('tb_user')->onDelete('cascade');
            $table->foreign('id_dokter')->references('id_dokter')->on('tb_dokter')->onDelete('cascade');
        });

        // Tabel tb_detail_konsultasi
        Schema::create('tb_detail_konsultasi', function (Blueprint $table) {
            $table->increments('id_detail_konsultasi'); // Primary key
            $table->unsignedInteger('id_konsultasi'); // Foreign key ke tabel tb_konsultasi
            $table->string('keluhan_pelanggan')->nullable();
            $table->string('saran_tindakan')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_konsultasi')->references('id_konsultasi')->on('tb_konsultasi')->onDelete('cascade');
        });

        // Tabel tb_feedback
        Schema::create('tb_feedback_konsultasi', function (Blueprint $table) {
            $table->increments('id_feedback_konsultasi'); // Primary key
            $table->unsignedInteger('id_konsultasi'); // Foreign key ke tabel tb_konsultasi
            $table->tinyInteger('rating')->unsigned(); // Rating dengan nilai maksimal 5
            $table->text('teks_feedback');
            $table->text('balasan_feedback')->nullable();
            $table->timestamps();
        
            // Foreign key constraints
            $table->foreign('id_konsultasi')->references('id_konsultasi')->on('tb_konsultasi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tabel sesuai urutan untuk menghindari masalah foreign key constraint
        Schema::dropIfExists('tb_feedback_konsultasi');
        Schema::dropIfExists('tb_detail_konsultasi');
        Schema::dropIfExists('tb_konsultasi');
    }
};
