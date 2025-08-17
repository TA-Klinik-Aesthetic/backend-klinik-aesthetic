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
        /*
        |----------------------------------------------------------------------
        | PEMBAYARAN → tambahkan id_penjualan_paket_treatment
        |----------------------------------------------------------------------
        | NOTE: kolom & FK akan ditambah jika belum ada.
        |       Jika sebelumnya kamu pernah menambahkan
        |       id_paket_treatment_pelanggan di pembayaran, kita lepas di sini.
        */
        Schema::table('tb_pembayaran', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_pembayaran', 'id_penjualan_paket_treatment')) {
                $table->unsignedInteger('id_penjualan_paket_treatment')->nullable()->after('id_penjualan_produk');
                $table->foreign('id_penjualan_paket_treatment', 'fk_bayar_penj_pkt')
                    ->references('id_penjualan_paket_treatment')->on('tb_penjualan_paket_treatment')
                    ->onDelete('set null');
            }
        });

        // Opsi: drop kolom lama kalau pernah ada
        if (Schema::hasColumn('tb_pembayaran', 'id_paket_treatment_pelanggan')) {
            Schema::table('tb_pembayaran', function (Blueprint $table) {
                // nama constraint lamanya bisa beda; coba drop jika ada
                try {
                    $table->dropForeign(['id_paket_treatment_pelanggan']);
                } catch (\Throwable $e) {
                }
                $table->dropColumn('id_paket_treatment_pelanggan');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback perubahan di pembayaran
        Schema::table('tb_pembayaran', function (Blueprint $table) {
            try {
                $table->dropForeign('fk_bayar_penj_pkt');
            } catch (\Throwable $e) {
            }
            if (Schema::hasColumn('tb_pembayaran', 'id_penjualan_paket_treatment')) {
                $table->dropColumn('id_penjualan_paket_treatment');
            }
        });
    }
};
