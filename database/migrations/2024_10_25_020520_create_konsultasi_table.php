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
        Schema::create('tb_detail_konsultasi', function (Blueprint $table) {
            $table->increments('id'); // Menggunakan tipe int untuk id
            $table->string('keluhan_pelanggan');
            $table->string('diagnosa_dokter');
            $table->string('saran_tindakan');
            $table->string('resep_obat');
            $table->string('catatan_tambahan');
            $table->timestamps();
        });

        Schema::create('tb_feedback', function (Blueprint $table) {
            $table->increments('id'); // Menggunakan tipe int untuk id
            $table->unsignedInteger('id_user');
            $table->foreign('id_user')->references('id')->on('tb_user')->onDelete('cascade');
            $table->tinyInteger('rating');
            $table->text('teks_feedback');
            $table->text('balasan_feedback');
            $table->timestamps();
        });

        Schema::create('tb_konsultasi', function (Blueprint $table) {
            $table->increments('id'); // Menggunakan tipe int untuk id
            $table->unsignedInteger('id_user')->nullable(); // Mengubah menjadi nullable
            $table->unsignedInteger('id_dokter')->nullable(false); // Tetap wajib ada
            $table->unsignedInteger('id_detail_konsultasi')->nullable(); // Mengubah menjadi nullable
            $table->unsignedInteger('id_feedback')->nullable(); // Mengubah menjadi nullable
            $table->dateTime('waktu_konsultasi')->nullable();
            $table->timestamps();
    
            // Tambahkan foreign key constraints
            $table->foreign('id_user')->references('id')->on('tb_user')->onDelete('cascade');
            $table->foreign('id_dokter')->references('id')->on('tb_dokter')->onDelete('cascade');
            $table->foreign('id_detail_konsultasi')->references('id')->on('tb_detail_konsultasi')->onDelete('cascade');
            $table->foreign('id_feedback')->references('id')->on('tb_feedback')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tb_konsultasi terlebih dahulu untuk menghindari masalah foreign key constraint
        Schema::dropIfExists('tb_konsultasi');
        Schema::dropIfExists('tb_detail_konsultasi');
        Schema::dropIfExists('tb_feedback');
    }
};
