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
            $table->unsignedInteger('id_user');
            $table->foreign('id_user')->references('id')->on('tb_user')->onDelete('cascade');
            $table->unsignedInteger('id_dokter');
            $table->foreign('id_dokter')->references('id')->on('tb_dokter')->onDelete('cascade');
            $table->unsignedInteger('id_detail_konsultasi');
            $table->foreign('id_detail_konsultasi')->references('id')->on('tb_detail_konsultasi')->onDelete('cascade');
            $table->unsignedInteger('id_feedback');
            $table->foreign('id_feedback')->references('id')->on('tb_feedback')->onDelete('cascade');
            $table->dateTime('waktu_konsultasi');
            $table->timestamps();
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
