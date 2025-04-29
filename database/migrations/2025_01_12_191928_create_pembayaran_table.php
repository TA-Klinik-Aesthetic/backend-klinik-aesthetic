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
        Schema::create('tb_pembayaran_treatment', function (Blueprint $table) {
            $table->increments('id_pembayaran_treatment'); // Primary key
            $table->unsignedInteger('id_booking_treatment'); // Foreign key ke tabel tb_user
            $table->enum('metode_pembayaran', ['Tunai', 'Non Tunai'])->default('Tunai');
            $table->enum('status_pembayaran', ['Belum Dibayar', 'Sudah Dibayar'])->default('Belum Dibayar');
            $table->decimal('total_bayar', 15, 2)->nullable(); // Ubah menjadi tipe double
            $table->string('gambar_bukti_transaksi')->nullable();
            $table->decimal('kembalian', 15, 2)->nullable(); // Ubah menjadi tipe double
            $table->timestamps();

            $table->foreign('id_booking_treatment')->references('id_booking_treatment')->on('tb_booking_treatment')->onDelete('cascade');
        });

        Schema::create('tb_pembayaran_produk', function (Blueprint $table) {
            $table->increments('id_pembayaran_produk'); // Primary key
            $table->unsignedInteger('id_penjualan_produk'); // Foreign key ke tabel tb_user
            $table->enum('metode_pembayaran', ['Tunai', 'Non Tunai'])->default('Tunai');
            $table->decimal('total_bayar', 15, 2)->nullable(); // Ubah menjadi tipe double
            $table->string('gambar_bukti_transaksi')->nullable();
            $table->decimal('kembalian', 15, 2)->nullable(); // Ubah menjadi tipe double
            $table->timestamps();

            $table->foreign('id_penjualan_produk')->references('id_penjualan_produk')->on('tb_penjualan_produk')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_pembayaran_treatment');
        Schema::dropIfExists('tb_pembayaran_produk');
    }
};
