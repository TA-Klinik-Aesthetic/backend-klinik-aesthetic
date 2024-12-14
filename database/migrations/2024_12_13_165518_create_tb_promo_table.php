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
        Schema::create('tb_promo', function (Blueprint $table) {
            $table->id('id_promo');
            $table->string('nama_promo');
            $table->text('deskripsi_promo')->nullable();
            $table->decimal('diskon_promo', 5, 2); // Contoh: 50.00
            $table->date('tanggal_mulai');
            $table->date('tanggal_berakhir');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_promo');
    }
};
