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
        Schema::create('tb_kompensasi', function (Blueprint $table) {
            $table->increments('id_kompensasi');
            $table->string('nama_kompensasi');
            $table->text('deskripsi_kompensasi')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_berakhir');
            $table->timestamps();
        });

        Schema::create('tb_komplain', function (Blueprint $table) {
            $table->integer('id_komplain');
            $table->unsignedInteger('id_user'); // Relasi ke tabel tb_dokter
            $table->text('teks_komplain')->nullable();
            $table->string('gambar_treatment');
            $table->text('balasan_komplain')->nullable();
            $table->unsignedInteger('id_kompensasi'); // Relasi ke tabel tb_dokter
            $table->timestamps();

            $table->foreign('id_kompensasi')->references('id_kompensasi')->on('tb_kompensasi')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('tb_user')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_kompensasi');
        Schema::dropIfExists('tb_komplain');
    }
};
