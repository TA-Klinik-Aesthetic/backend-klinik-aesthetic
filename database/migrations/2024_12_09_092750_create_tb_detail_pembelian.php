<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tb_detail_pembelian_produk', function (Blueprint $table) {
            $table->increments('id_detail_pembelian_produk');
            $table->unsignedInteger('id_pembelian_produk');
            $table->unsignedInteger('id_produk');
            $table->integer('jumlah_produk');
            $table->decimal('harga_pembelian_produk', 15, 2);
            $table->timestamps();

            $table->foreign('id_pembelian_produk')
                ->references('id_pembelian_produk')
                ->on('tb_pembelian_produk')
                ->onDelete('cascade');
            $table->foreign('id_produk')
                ->references('id_produk')
                ->on('tb_produk')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_detail_pembelian_produk');
    }

};
