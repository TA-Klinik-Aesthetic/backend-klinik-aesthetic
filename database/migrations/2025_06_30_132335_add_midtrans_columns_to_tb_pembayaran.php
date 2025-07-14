<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMidtransColumnsToTbPembayaran extends Migration
{
    public function up()
    {
        Schema::table('tb_pembayaran', function (Blueprint $table) {
            // Cek apakah kolom sudah ada sebelum menambahkan
            if (!Schema::hasColumn('tb_pembayaran', 'snap_token')) {
                $table->string('snap_token')->nullable();
            }
            if (!Schema::hasColumn('tb_pembayaran', 'snap_url')) {
                $table->text('snap_url')->nullable();
            }
            if (!Schema::hasColumn('tb_pembayaran', 'order_id')) {
                $table->string('order_id')->nullable();
            }
            if (!Schema::hasColumn('tb_pembayaran', 'transaction_id')) {
                $table->string('transaction_id')->nullable();
            }
            if (!Schema::hasColumn('tb_pembayaran', 'transaction_status')) {
                $table->string('transaction_status')->nullable();
            }
            if (!Schema::hasColumn('tb_pembayaran', 'payment_type')) {
                $table->string('payment_type')->nullable();
            }
            if (!Schema::hasColumn('tb_pembayaran', 'va_number')) {
                $table->string('va_number')->nullable();
            }
            if (!Schema::hasColumn('tb_pembayaran', 'bank')) {
                $table->string('bank')->nullable();
            }
            if (!Schema::hasColumn('tb_pembayaran', 'midtrans_response')) {
                $table->text('midtrans_response')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('tb_pembayaran', function (Blueprint $table) {
            $columns = [
                'snap_token', 'snap_url', 'order_id', 'transaction_id',
                'transaction_status', 'payment_type', 'va_number',
                'bank', 'midtrans_response'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('tb_pembayaran', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
