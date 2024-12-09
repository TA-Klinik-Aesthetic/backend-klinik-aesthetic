<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tb_pembelian_produk', function (Blueprint $table) {
            $table->increments('id_pembelian_produk');
            $table->unsignedInteger('id_user');
            $table->dateTime('tanggal_pembelian')->default(now());
            $table->decimal('harga_total', 15, 2);
            $table->decimal('potongan_harga', 15, 2)->default(0);
            $table->decimal('harga_akhir', 15, 2);
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('tb_user')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pembelian_produk');
    }
};
