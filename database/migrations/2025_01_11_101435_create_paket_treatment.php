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
        /**
         * 1) tb_paket_treatment
         */
        Schema::create('tb_paket_treatment', function (Blueprint $t) {
            $t->increments('id_paket_treatment');
            $t->string('nama_paket_treatment', 100);
            $t->text('deskripsi_paket_treatment')->nullable();
            $t->decimal('harga_paket_treatment', 12, 2);
            $t->timestamps();
        });

        /**
         * 2) tb_detail_paket_treatment
         */
        Schema::create('tb_detail_paket_treatment', function (Blueprint $t) {
            $t->increments('id_detail_paket_treatment');
            $t->unsignedInteger('id_paket_treatment');
            $t->unsignedInteger('id_treatment');
            $t->unsignedInteger('jumlah_penggunaan');
            $t->timestamps();

            // FK (pakai nama pendek)
            $t->foreign('id_paket_treatment', 'fk_dpt_paket')
                ->references('id_paket_treatment')->on('tb_paket_treatment')
                ->onDelete('cascade');

            $t->foreign('id_treatment', 'fk_dpt_treat')
                ->references('id_treatment')->on('tb_treatment');

            // Unique agar 1 treatment tidak dobel dalam 1 paket
            $t->unique(['id_paket_treatment', 'id_treatment'], 'uq_dpt_paket_treatment');
        });

          /*
        |----------------------------------------------------------------------
        | PENJUALAN PAKET (BARU)
        |----------------------------------------------------------------------
        */
        if (!Schema::hasTable('tb_penjualan_paket_treatment')) {
            Schema::create('tb_penjualan_paket_treatment', function (Blueprint $table) {
                $table->increments('id_penjualan_paket_treatment');
                $table->unsignedInteger('id_user');
                $table->dateTime('tanggal_pembelian');
                $table->decimal('harga_total', 12, 2)->default(0);
                $table->unsignedInteger('id_promo')->nullable();
                $table->decimal('potongan_harga', 12, 2)->default(0);
                $table->decimal('besaran_pajak', 12, 2)->default(0);
                $table->decimal('harga_akhir', 12, 2)->default(0);
                $table->timestamps();

                $table->foreign('id_user', 'fk_penj_pkt_user')
                      ->references('id_user')->on('tb_user')
                      ->onDelete('restrict');

                // Jika tabel promo-mu bernama tb_promo dan PK: id_promo
                $table->foreign('id_promo', 'fk_penj_pkt_promo')
                      ->references('id_promo')->on('tb_promo')
                      ->onDelete('set null');
            });
        }

        if (!Schema::hasTable('tb_detail_penjualan_paket_treatment')) {
            Schema::create('tb_detail_penjualan_paket_treatment', function (Blueprint $table) {
                $table->increments('id_detail_penjualan_paket_treatment');
                $table->unsignedInteger('id_penjualan_paket_treatment');
                $table->unsignedInteger('id_paket_treatment');
                $table->decimal('harga_paket_treatment', 12, 2)->default(0);
                $table->timestamps();

                $table->foreign('id_penjualan_paket_treatment', 'fk_det_penj_pkt_penj')
                      ->references('id_penjualan_paket_treatment')->on('tb_penjualan_paket_treatment')
                      ->onDelete('cascade');

                $table->foreign('id_paket_treatment', 'fk_det_penj_pkt_pkt')
                      ->references('id_paket_treatment')->on('tb_paket_treatment')
                      ->onDelete('restrict');
            });
        }


        /**
         * 3) tb_paket_treatment_pelanggan
         */
        Schema::create('tb_paket_treatment_pelanggan', function (Blueprint $t) {
            $t->increments('id_paket_treatment_pelanggan');
            $t->unsignedInteger('id_user');
            $t->unsignedInteger('id_paket_treatment');
            $t->timestamps();

            $t->foreign('id_user', 'fk_ptp_user')
                ->references('id_user')->on('tb_user');

            $t->foreign('id_paket_treatment', 'fk_ptp_paket')
                ->references('id_paket_treatment')->on('tb_paket_treatment');
        });

        /**
         * 4) tb_detail_paket_treatment_pelanggan
         */
        Schema::create('tb_detail_paket_treatment_pelanggan', function (Blueprint $t) {
            $t->increments('id_detail_paket_treatment_pelanggan');
            $t->unsignedInteger('id_paket_treatment_pelanggan');
            $t->unsignedInteger('id_treatment');
            $t->unsignedInteger('jumlah_penggunaan');           // kuota total
            $t->timestamps();

            $t->foreign('id_paket_treatment_pelanggan', 'fk_dptp_ptp')
                ->references('id_paket_treatment_pelanggan')->on('tb_paket_treatment_pelanggan')
                ->onDelete('cascade');

            $t->foreign('id_treatment', 'fk_dptp_treat')
                ->references('id_treatment')->on('tb_treatment')
                ->onDelete('restrict');

            $t->unique(['id_paket_treatment_pelanggan', 'id_treatment'], 'uq_dptp_pelanggan_treatment');
        });

        

        /**
         * 5) tb_booking_treatment_paket (HEADER)
         */
        Schema::create('tb_booking_treatment_paket', function (Blueprint $t) {
            $t->increments('id_booking_treatment_paket');
            $t->unsignedInteger('id_user');
            $t->dateTime('waktu_treatment');
            $t->unsignedInteger('id_dokter')->nullable();
            $t->unsignedInteger('id_beautician')->nullable();

            $t->enum('status_booking_treatment', [
                'Verifikasi',
                'Berhasil dibooking',
                'Treatment dimulai',
                'Dibatalkan',
                'Selesai',
            ])->default('Verifikasi');

            $t->dateTime('treatment_mulai')->nullable();
            $t->dateTime('treatment_selesai')->nullable();
            $t->timestamps();

            $t->foreign('id_user', 'fk_bpkt_user')->references('id_user')->on('tb_user');
            $t->foreign('id_dokter', 'fk_bpkt_dokter')->references('id_dokter')->on('tb_dokter');
            $t->foreign('id_beautician', 'fk_bpkt_beaut')->references('id_beautician')->on('tb_beautician');
        });

        /**
         * 6) tb_detail_booking_treatment_paket (DETAIL)
         */
        Schema::create('tb_detail_booking_treatment_paket', function (Blueprint $t) {
            $t->increments('id_detail_booking_treatment_paket');
            $t->unsignedInteger('id_booking_treatment_paket');
            $t->unsignedInteger('id_paket_treatment_pelanggan');
            $t->unsignedInteger('id_treatment'); // sesuai revisi
            $t->unsignedInteger('jumlah_dipakai')->default(1);
            $t->timestamps();

            $t->foreign('id_booking_treatment_paket', 'fk_dbpkt_booking')
                ->references('id_booking_treatment_paket')->on('tb_booking_treatment_paket')
                ->onDelete('cascade');

            $t->foreign('id_paket_treatment_pelanggan', 'fk_dbpkt_ptp')
                ->references('id_paket_treatment_pelanggan')->on('tb_paket_treatment_pelanggan');

            $t->foreign('id_treatment', 'fk_dbpkt_treat')
                ->references('id_treatment')->on('tb_treatment');

            $t->unique(
                ['id_booking_treatment_paket', 'id_paket_treatment_pelanggan', 'id_treatment'],
                'uq_dbpkt_booking_ptp_treatment'
            );
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('tb_detail_booking_treatment_paket');
        Schema::dropIfExists('tb_booking_treatment_paket');
        Schema::dropIfExists('tb_detail_penjualan_paket_treatment');
        Schema::dropIfExists('tb_penjualan_paket_treatment');
        Schema::dropIfExists('tb_detail_paket_treatment_pelanggan');
        Schema::dropIfExists('tb_paket_treatment_pelanggan');
        Schema::dropIfExists('tb_detail_paket_treatment');
        Schema::dropIfExists('tb_paket_treatment');
    }
};
