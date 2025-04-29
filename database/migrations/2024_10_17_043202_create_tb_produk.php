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
        Schema::create('tb_jenis_treatment', function (Blueprint $table) {
            $table->increments('id_jenis_treatment'); // Menggunakan tipe int untuk id
            $table->string('nama_jenis_treatment', 50);
            $table->timestamps();
        });

        // Create tb_kategori table
        Schema::create('tb_kategori', function (Blueprint $table) {
            $table->increments('id_kategori'); // Primary Key
            $table->string('nama_kategori', 255);
            $table->timestamps();
        });

        // Create tb_produk table
        Schema::create('tb_produk', function (Blueprint $table) {
            $table->increments('id_produk'); // Primary Key
            $table->unsignedInteger('id_kategori'); // Foreign Key
            // $table->unsignedInteger('id_jenis_treatment')->nullable();
            $table->string('nama_produk', 255);
            $table->text('deskripsi_produk')->nullable();
            $table->decimal('harga_produk', 15, 2);
            $table->integer('stok_produk');
            $table->enum('status_produk', ['Tersedia', 'Habis']);
            $table->string('gambar_produk', 255);

            $table->timestamps(); // Created at & Updated at

            // Set Foreign Key Constraint
            $table->foreign('id_kategori')->references('id_kategori')->on('tb_kategori')->onDelete('cascade');
            // $table->foreign('id_jenis_treatment')->references('id_jenis_treatment')->on('tb_jenis_treatment')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tb_produk first because it depends on tb_kategori
        Schema::dropIfExists('tb_jenis_treatment');
        Schema::dropIfExists('tb_produk');
        Schema::dropIfExists('tb_kategori');
    }
};
