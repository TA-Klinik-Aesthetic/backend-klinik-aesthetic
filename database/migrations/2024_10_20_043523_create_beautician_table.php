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
            $table->increments('id_beautician'); // Menggunakan tipe int untuk id
            $table->string('nama_beautician',50);
            $table->string('no_telp',50);
            $table->string('email_beautician',50);
            $table->string('NIP', 50);
            $table->timestamps();
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
