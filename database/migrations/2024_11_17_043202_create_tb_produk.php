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
        // Create tb_kategori table
        Schema::create('tb_kategori', function (Blueprint $table) {
            $table->id('id_kategori'); // Primary Key
            $table->string('nama_kategori', 255);
            $table->string('deskripsi_kategori', 255)->nullable();
            $table->timestamps(); // Created at & Updated at
        });

        // Create tb_produk table
        Schema::create('tb_produk', function (Blueprint $table) {
            $table->id('id_produk'); // Primary Key
            $table->unsignedBigInteger('id_kategori'); // Foreign Key
            $table->string('nama_produk', 255);
            $table->text('deskripsi_produk')->nullable();
            $table->decimal('harga_produk', 10, 2);
            $table->integer('stok_produk');
            $table->timestamps(); // Created at & Updated at

            // Set Foreign Key Constraint
            $table->foreign('id_kategori')
                ->references('id_kategori')
                ->on('tb_kategori')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tb_produk first because it depends on tb_kategori
        Schema::dropIfExists('tb_produk');
        Schema::dropIfExists('tb_kategori');
    }
};
