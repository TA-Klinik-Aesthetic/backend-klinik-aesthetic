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
        Schema::create('tb_pembayaran', function (Blueprint $table) {
            $table->increments('id_pembayaran');
            $table->unsignedInteger('id_booking_treatment')->nullable();
            $table->unsignedInteger('id_penjualan_produk')->nullable();
            $table->enum('metode_pembayaran', ['Tunai', 'QRIS', 'Virtual Account', 'E-Wallet', 'Non Tunai'])->default('Tunai');
            $table->decimal('uang', 15, 2)->nullable();
            $table->decimal('kembalian', 15, 2)->nullable();
            $table->enum('status_pembayaran', ['Belum Dibayar', 'Sudah Dibayar', 'Dibatalkan', 'Pending', 'Berhasil', 'Gagal'])->default('Belum Dibayar');
            $table->dateTime('waktu_pembayaran')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('order_id')->nullable();
            $table->string('snap_token')->nullable();
            $table->string('snap_url')->nullable();
            $table->json('payment_details')->nullable();
            $table->timestamps();

            $table->foreign('id_booking_treatment')
                ->references('id_booking_treatment')->on('tb_booking_treatment')
                ->onDelete('cascade');
            $table->foreign('id_penjualan_produk')
                ->references('id_penjualan_produk')->on('tb_penjualan_produk')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_pembayaran');
    }
};
