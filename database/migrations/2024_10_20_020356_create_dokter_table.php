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
        Schema::create('tb_dokter', function (Blueprint $table) {
            $table->increments('id_dokter'); // Menggunakan tipe int untuk id
            $table->string('nama_dokter', 50);
            $table->string('no_telp', 50);
            $table->string('email_dokter', 50);
            $table->string('NIP', 50);
            $table->timestamps();
        });

        Schema::create('tb_jadwal_praktik_dokter', function (Blueprint $table) {
            $table->increments('id_jadwal_praktik_dokter');
            $table->unsignedInteger('id_dokter'); // Relasi ke tabel tb_dokter
            $table->enum('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']);
            $table->date('tgl_kerja'); 
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->timestamps();
            
            $table->foreign('id_dokter')->references('id_dokter')->on('tb_dokter')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_dokter');
        Schema::dropIfExists('tb_jadwal_praktik_dokter');
    }
};
