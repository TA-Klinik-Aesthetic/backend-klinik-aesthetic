<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel utama
        Schema::create('tb_treatment', function (Blueprint $table) {
            $table->increments('id_treatment');
            $table->unsignedInteger('id_jenis_treatment');
            $table->string('nama_treatment');
            $table->text('deskripsi_treatment');
            $table->decimal('biaya_treatment', 15, 2);
            $table->time('estimasi_treatment');
            $table->string('gambar_treatment');
            $table->timestamps();
        });

        Schema::create('tb_kompensasi', function (Blueprint $table) {
            $table->increments('id_kompensasi');
            $table->unsignedInteger('id_treatment');
            $table->string('nama_kompensasi');
            $table->text('deskripsi_kompensasi')->nullable();
            $table->timestamps();
        });

        Schema::create('tb_booking_treatment', function (Blueprint $table) {
            $table->increments('id_booking_treatment');
            $table->unsignedInteger('id_user');
            $table->dateTime('waktu_treatment');
            $table->unsignedInteger('id_dokter')->nullable();
            $table->unsignedInteger('id_beautician')->nullable();
            $table->enum('status_booking_treatment', ['Verifikasi', 'Berhasil dibooking', 'Dibatalkan', 'Selesai'])->default('Verifikasi')->nullable();
            $table->decimal('harga_total', 15, 2)->nullable();
            $table->unsignedInteger('id_promo')->nullable();
            $table->decimal('potongan_harga', 15, 2)->nullable();
            $table->decimal('besaran_pajak', 15, 2)->default(0); // Kolom untuk pajak
            $table->decimal('harga_akhir_treatment', 15, 2)->nullable();
            // $table->enum('status_pembayaran', ['Belum Dibayar', 'Sudah Dibayar'])->default('Belum Dibayar');
            $table->timestamps();
        });

        Schema::create('tb_kompensasi_diberikan', function (Blueprint $table) {
            $table->increments('id_kompensasi_diberikan');
            $table->unsignedInteger('id_komplain')->nullable();
            $table->unsignedInteger('id_kompensasi')->nullable();
            $table->string('kode_kompensasi')->unique()->nullable();
            $table->date('tanggal_berakhir_kompensasi')->nullable();
            $table->enum('status_kompensasi', ['Belum Digunakan', 'Sudah Digunakan', 'Sudah Kadaluwarsa'])->default('Belum Digunakan');
            $table->dateTime('tanggal_pemakaian_kompensasi')->nullable();
            $table->timestamps();
        });

        Schema::create('tb_detail_booking_treatment', function (Blueprint $table) {
            $table->increments('id_detail_booking_treatment');
            $table->unsignedInteger('id_booking_treatment');
            $table->unsignedInteger('id_treatment');
            $table->decimal('biaya_treatment', 15, 2);
            $table->unsignedInteger('id_kompensasi_diberikan')->nullable();
            $table->timestamps();
        });

        Schema::create('tb_komplain', function (Blueprint $table) {
            $table->increments('id_komplain');
            $table->unsignedInteger('id_user');
            $table->unsignedInteger('id_booking_treatment');
            $table->unsignedInteger('id_detail_booking_treatment');
            $table->text('teks_komplain')->nullable();
            $table->text('gambar_komplain')->nullable();
            $table->text('balasan_komplain')->nullable();
            $table->enum('pemberian_kompensasi', ['Tidak ada pemberian', 'Sudah diberikan'])->default('Tidak ada pemberian');
            $table->timestamps();
        });

        Schema::create('tb_feedback_treatment', function (Blueprint $table) {
            $table->increments('id_feedback_treatment');
            $table->unsignedInteger('id_detail_booking_treatment');
            $table->tinyInteger('rating')->unsigned()->nullable();
            $table->text('teks_feedback')->nullable();
            $table->timestamps();
        });

        // Tambahkan Foreign Key setelah semua tabel dibuat
        Schema::table('tb_treatment', function (Blueprint $table) {
            $table->foreign('id_jenis_treatment')->references('id_jenis_treatment')->on('tb_jenis_treatment')->onDelete('cascade');
        });
        Schema::table('tb_kompensasi', function (Blueprint $table) {
            $table->foreign('id_treatment')->references('id_treatment')->on('tb_treatment')->onDelete('cascade');
        });
        Schema::table('tb_booking_treatment', function (Blueprint $table) {
            $table->foreign('id_user')->references('id_user')->on('tb_user')->onDelete('cascade');
            $table->foreign('id_dokter')->references('id_dokter')->on('tb_dokter')->onDelete('set null');
            $table->foreign('id_beautician')->references('id_beautician')->on('tb_beautician')->onDelete('set null');
            $table->foreign('id_promo')->references('id_promo')->on('tb_promo')->onDelete('cascade');
        });
        Schema::table('tb_kompensasi_diberikan', function (Blueprint $table) {
            $table->foreign('id_komplain')->references('id_komplain')->on('tb_komplain')->onDelete('cascade');
            $table->foreign('id_kompensasi')->references('id_kompensasi')->on('tb_kompensasi')->onDelete('cascade');
        });
        Schema::table('tb_detail_booking_treatment', function (Blueprint $table) {
            $table->foreign('id_booking_treatment')->references('id_booking_treatment')->on('tb_booking_treatment')->onDelete('cascade');
            $table->foreign('id_treatment')->references('id_treatment')->on('tb_treatment')->onDelete('cascade');
            $table->foreign('id_kompensasi_diberikan')->references('id_kompensasi_diberikan')->on('tb_kompensasi_diberikan')->onDelete('set null');
        });
        Schema::table('tb_komplain', function (Blueprint $table) {
            $table->foreign('id_user')->references('id_user')->on('tb_user')->onDelete('cascade');
            $table->foreign('id_booking_treatment')->references('id_booking_treatment')->on('tb_booking_treatment')->onDelete('cascade');
            $table->foreign('id_detail_booking_treatment')->references('id_detail_booking_treatment')->on('tb_detail_booking_treatment')->onDelete('cascade');
        });
        Schema::table('tb_feedback_treatment', function (Blueprint $table) {
            $table->foreign('id_detail_booking_treatment')->references('id_detail_booking_treatment')->on('tb_detail_booking_treatment')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_feedback_treatment');
        Schema::dropIfExists('tb_komplain');
        Schema::dropIfExists('tb_detail_booking_treatment');
        Schema::dropIfExists('tb_kompensasi_diberikan');
        Schema::dropIfExists('tb_booking_treatment');
        Schema::dropIfExists('tb_kompensasi');
        Schema::dropIfExists('tb_treatment');
    }
};
