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
        Schema::create('tb_fcm_token', function (Blueprint $table) {
            $table->id('id_fcm_token');
            $table->unsignedInteger('id_user');
            $table->string('device_token', 255);
            $table->string('device_type', 50)->nullable(); // 'android', 'ios'
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('tb_user')->onDelete('cascade');
            $table->unique(['id_user', 'device_token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_fcm_token');
    }
};
