<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Promo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpirePromoCommand extends Command
{
    protected $signature = 'promo:expire';
    protected $description = 'Menonaktifkan promo yang sudah berakhir';

    public function handle()
    {
        $now = Carbon::now();
        Log::info("[promo:expire] running at {$now}");

        $expiredPromos = Promo::where('tanggal_berakhir', '<=', $now)
                              ->where('status_promo', 'Aktif')
                              ->get();

        foreach ($expiredPromos as $promo) {
            $promo->update(['status_promo' => 'Tidak Aktif']);
            Log::info("[promo:expire] promo id {$promo->id_promo} dinonaktifkan");
        }

        $this->info(count($expiredPromos) . ' promo telah dinonaktifkan.');

        Log::info(sprintf(
            "[promo:expire] selesai, total %d promo dinonaktifkan",
            $expiredPromos->count()
        ));
    }
}