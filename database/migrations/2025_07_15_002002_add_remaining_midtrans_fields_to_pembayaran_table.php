<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemainingMidtransFieldsToPembayaranTable extends Migration
{
    public function up()
    {
        Schema::table('tb_pembayaran', function (Blueprint $table) {
            // Hanya tambah jika field belum ada
            if (!Schema::hasColumn('tb_pembayaran', 'gross_amount')) {
                $table->decimal('gross_amount', 15, 2)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('tb_pembayaran', function (Blueprint $table) {
            if (Schema::hasColumn('tb_pembayaran', 'gross_amount')) {
                $table->dropColumn('gross_amount');
            }
        });
    }
}
