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

        // Tabel tb_treatment
        Schema::create('tb_treatment', function (Blueprint $table) {
            $table->increments('id_treatment'); // Primary key
            $table->unsignedInteger('id_jenis_treatment'); // Foreign key ke tabel tb_jenis_treatment
            $table->string('nama_treatment');
            $table->text('deskripsi_treatment');
            $table->decimal('biaya_treatment', 15, 2); // Ubah menjadi tipe double
            $table->time('estimasi_treatment');
            $table->string('gambar_treatment');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_jenis_treatment')->references('id_jenis_treatment')->on('tb_jenis_treatment')->onDelete('cascade');
        });

        Schema::create('tb_kompensasi', function (Blueprint $table) {
            $table->increments('id_kompensasi');
            $table->unsignedInteger('id_treatment'); // Kompensasi hanya untuk treatment tertentu
            $table->string('nama_kompensasi');
            $table->text('deskripsi_kompensasi')->nullable();
            $table->timestamps();
        
            $table->foreign('id_treatment')->references('id_treatment')->on('tb_treatment')->onDelete('cascade');
        });

        // Tabel tb_booking_treatment
        Schema::create('tb_booking_treatment', function (Blueprint $table) {
            $table->increments('id_booking_treatment'); // Primary key
            $table->unsignedInteger('id_user'); // Foreign key ke tabel tb_user
            $table->dateTime('waktu_treatment');
            $table->enum('status_booking_treatment', ['Verifikasi', 'Berhasil dibooking', 'Dibatalkan', 'Selesai'])->default('Verifikasi')->nullable();
            $table->decimal('harga_total', 15, 2)->nullable(); // Ubah menjadi tipe double
            $table->unsignedInteger('id_promo')->nullable(); // Foreign key ke tabel tb_user
            $table->decimal('potongan_harga', 15, 2)->nullable(); // Ubah menjadi tipe double
            $table->decimal('harga_akhir_treatment', 15, 2)->nullable(); // Ubah menjadi tipe double
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_user')->references('id_user')->on('tb_user')->onDelete('cascade');
            $table->foreign('id_promo')->references('id_promo')->on('tb_promo')->onDelete('cascade');
        });

        Schema::create('tb_komplain', function (Blueprint $table) {
            $table->increments('id_komplain');
            $table->unsignedInteger('id_user');
            $table->text('teks_komplain')->nullable();
            $table->text('gambar_komplain')->nullable();
            $table->string('gambar_bukti_transaksi')->nullable();
            $table->text('balasan_komplain')->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('tb_user')->onDelete('cascade');
        });

        // Tabel kompensasi
        Schema::create('tb_kompensasi_diberikan', function (Blueprint $table) {
            $table->increments('id_kompensasi_diberikan');
            $table->unsignedInteger('id_komplain');
            $table->unsignedInteger('id_kompensasi');
            $table->string('kode_kompensasi')->unique(); // Kode unik per pemberian
            $table->date('tanggal_berakhir_kompensasi')->nullable();
            $table->enum('status_kompensasi', ['Belum Digunakan', 'Sudah Digunakan', 'Sudah Kadaluwarsa'])->default('Belum Digunakan');
            $table->dateTime('tanggal_pemakaian_kompensasi')->nullable();
            $table->timestamps();

            $table->foreign('id_komplain')->references('id_komplain')->on('tb_komplain')->onDelete('cascade');
            $table->foreign('id_kompensasi')->references('id_kompensasi')->on('tb_kompensasi')->onDelete('cascade');
        });

        // Tabel tb_detail_booking_treatment
        Schema::create('tb_detail_booking_treatment', function (Blueprint $table) {
            $table->increments('id_detail_booking_treatment'); // Primary key
            $table->unsignedInteger('id_booking_treatment'); // FK ke tb_booking_treatment
            $table->unsignedInteger('id_treatment'); // FK ke tb_treatment
            $table->decimal('biaya_treatment', 15, 2);
            $table->unsignedInteger('id_dokter')->nullable(); // FK ke tb_dokter
            $table->unsignedInteger('id_beautician')->nullable(); // FK ke tb_beautician
            $table->unsignedInteger('id_kompensasi_diberikan')->nullable(); // FK ke kompensasi_diberikan
            $table->timestamps();

            $table->foreign('id_booking_treatment')->references('id_booking_treatment')->on('tb_booking_treatment')->onDelete('cascade');
            $table->foreign('id_treatment')->references('id_treatment')->on('tb_treatment')->onDelete('cascade');
            $table->foreign('id_dokter')->references('id_dokter')->on('tb_dokter')->onDelete('set null');
            $table->foreign('id_beautician')->references('id_beautician')->on('tb_beautician')->onDelete('set null');
            $table->foreign('id_kompensasi_diberikan')->references('id_kompensasi_diberikan')->on('tb_kompensasi_diberikan')->onDelete('set null');
        });


        // Schema::create('tb_detail_booking_produk', function (Blueprint $table) {
        //     $table->increments('id_detail_booking_produk');
        //     $table->unsignedInteger('id_detail_booking_treatment'); // Relasi ke detail booking treatment
        //     $table->unsignedInteger('id_produk'); // Relasi ke produk
        //     $table->integer('jumlah_produk')->nullable();
        //     $table->decimal('harga_produk', 15, 2)->nullable(); // Simpan harga produk saat booking (opsional)
        //     $table->decimal('harga_total_produk', 15, 2)->nullable();
        //     $table->timestamps();

        //     // Foreign key constraints
        //     $table->foreign('id_detail_booking_treatment')->references('id_detail_booking_treatment')->on('tb_detail_booking_treatment')->onDelete('cascade');
        //     $table->foreign('id_produk')->references('id_produk')->on('tb_produk')->onDelete('cascade');
        // });

        // Tabel tb_feedback
        Schema::create('tb_feedback_treatment', function (Blueprint $table) {
            $table->increments('id_feedback_treatment'); // Primary key
            $table->unsignedInteger('id_detail_booking_treatment'); // Foreign key ke tabel tb_detail_booking_treatment
            $table->tinyInteger('rating')->unsigned()->nullable(); // Rating dengan nilai maksimal 5
            $table->text('teks_feedback')->nullable();
            $table->text('balasan_feedback')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_detail_booking_treatment')->references('id_detail_booking_treatment')->on('tb_detail_booking_treatment')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_treatment');
        Schema::dropIfExists('tb_kompensasi');
        Schema::dropIfExists('tb_booking_treatment');
        Schema::dropIfExists('tb_komplain');
        // Schema::dropIfExists('tb_detail_booking_produk');
        Schema::dropIfExists('tb_detail_booking_treatment');
        Schema::dropIfExists('tb_kompensasi_diberikan');
    }
};
