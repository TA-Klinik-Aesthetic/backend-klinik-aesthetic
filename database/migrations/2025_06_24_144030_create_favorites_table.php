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
        Schema::create('tb_favorite', function (Blueprint $table) {
            $table->increments('id_favorite');
            $table->unsignedInteger('id_user');
            $table->unsignedInteger('id_dokter')->nullable();
            $table->unsignedInteger('id_produk')->nullable();
            $table->unsignedInteger('id_treatment')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_user')->references('id_user')->on('tb_user')->onDelete('cascade');
            $table->foreign('id_dokter')->references('id_dokter')->on('tb_dokter')->onDelete('cascade');
            $table->foreign('id_produk')->references('id_produk')->on('tb_produk')->onDelete('cascade');
            $table->foreign('id_treatment')->references('id_treatment')->on('tb_treatment')->onDelete('cascade');

            // Ensure a user can only favorite an item once
            $table->unique(['id_user', 'id_dokter']);
            $table->unique(['id_user', 'id_produk']);
            $table->unique(['id_user', 'id_treatment']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_favorite');
    }
};
