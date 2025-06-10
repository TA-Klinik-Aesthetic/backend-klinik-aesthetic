<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tb_penjualan_produk', function (Blueprint $table) {
            $table->increments('id_penjualan_produk');
            $table->unsignedInteger('id_user');
            $table->dateTime('tanggal_pembelian')->default(now());
            $table->decimal('harga_total', 15, 2);
            $table->unsignedInteger('id_promo')->nullable(); // Foreign key ke tabel tb_user
            $table->decimal('potongan_harga', 15, 2)->nullable()->default(0);
            $table->decimal('besaran_pajak', 15, 2)->default(0); // Kolom untuk pajak
            $table->decimal('harga_akhir', 15, 2);
            // $table->enum('status_pembayaran', ['Belum Dibayar', 'Sudah Dibayar'])->default('Belum Dibayar');
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('tb_user')->onDelete('cascade');
            $table->foreign('id_promo')->references('id_promo')->on('tb_promo')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_penjualan_produk');
    }
};
