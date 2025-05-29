<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tb_keranjang_pembelian', function (Blueprint $table) {
             $table->engine = 'InnoDB';

            $table->increments('id_keranjang_pembelian');
            $table->unsignedInteger('id_user');
            $table->unsignedInteger('id_produk');
            $table->integer('jumlah')->default(1);
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('tb_user')->onDelete('cascade');
            $table->foreign('id_produk')->references('id_produk')->on('tb_produk')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_keranjang_pembelian');
    }
};
