<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom untuk mendukung integrasi Midtrans (VA, GoPay, ShopeePay, QRIS).
     */
    public function up(): void
    {
        Schema::table('tb_pembayaran', function (Blueprint $table) {
            $table->string('order_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('transaction_status')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('va_number')->nullable();
            $table->string('bank')->nullable();
            $table->string('qr_url')->nullable();
            $table->string('pdf_url')->nullable();
            $table->string('payment_code')->nullable();
            $table->decimal('gross_amount', 15, 2)->nullable();
            $table->string('currency', 10)->default('IDR');
            $table->longText('midtrans_response')->nullable();
        });
    }

    /**
     * Kembalikan perubahan jika rollback.
     */
    public function down(): void
    {
        // Schema::dropIfExists('tb_pembayaran');
    }
};
