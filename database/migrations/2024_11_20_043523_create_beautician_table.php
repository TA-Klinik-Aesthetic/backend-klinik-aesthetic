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
        Schema::create('tb_beautician', function (Blueprint $table) {
            $table->increments('id'); // Menggunakan tipe int untuk id
            $table->string('nama_beautician',50);
            $table->string('no_telp',50);
            $table->string('email_beautician',50);
            $table->string('NIP', 50);
            $table->timestamps();
        });

        Schema::create('tb_jadwal_praktik_beautician', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_beautician'); // Relasi ke tabel tb_dokter
            $table->enum('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']);
            $table->date('tgl_kerja'); 
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->timestamps();
            
            $table->foreign('id_beautician')->references('id')->on('tb_beautician')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_beautician');
        Schema::dropIfExists('tb_jadwal_praktik_beautician');
    }
};
