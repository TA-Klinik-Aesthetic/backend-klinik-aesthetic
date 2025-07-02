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
        Schema::create('tb_jadwal_treatment', function (Blueprint $table) {
            $table->increments('id_jadwal_treatment');
            $table->date('tanggal_treatment');
            $table->timestamps();
        });

        Schema::create('tb_detail_jadwal_treatment', function (Blueprint $table) {
            $table->increments('id_detail_jadwal_treatment');
            $table->unsignedInteger('id_jadwal_treatment')
                  ->constrained('jadwal_treatment', 'id_jadwal_treatment')
                  ->onDelete('cascade');
            $table->time('waktu_tersedia');
            $table->unsignedTinyInteger('maks_booking')->default(2);
            $table->enum('status_jadwal', ['tersedia','sudah dipesan'])->default('tersedia');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_detail_jadwal_treatment');
        Schema::dropIfExists('tb_jadwal_treatment');
    }
};
