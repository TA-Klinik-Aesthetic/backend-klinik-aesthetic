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
        Schema::create('tb_notifikasi', function (Blueprint $table) {
            $table->id('id_notifikasi');
            $table->unsignedBigInteger('id_user');
            $table->string('judul', 255);
            $table->text('pesan');
            $table->string('jenis', 50); // 'treatment', 'konsultasi', 'produk', 'promo'
            $table->unsignedBigInteger('id_referensi')->nullable(); // ID referensi ke tabel lain
            $table->string('status', 50)->default('unread'); // 'read', 'unread'
            $table->string('gambar')->nullable(); // Path ke gambar notifikasi
            $table->timestamp('tanggal_notifikasi')->useCurrent();
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('tb_user')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_notifikasi');
    }
};
