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
            $table->id('id_promo'); // ID promo dengan auto increment
            $table->string('judul_promo');
            $table->text('deskripsi_promo');
            $table->text('keterangan_promo');
            $table->dateTime('tenggat_waktu_promosi');
            $table->timestamps(); // Untuk created_at dan updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_promo');
    }
};
