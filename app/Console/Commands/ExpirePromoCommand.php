<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Promo;
use Carbon\Carbon;

class ExpirePromoCommand extends Command
{
    protected $signature = 'promo:expire';
    protected $description = 'Menonaktifkan promo yang sudah berakhir';

    public function handle()
    {
        $now = Carbon::now();
        $expiredPromos = Promo::where('tanggal_berakhir', '<=', $now)
                              ->where('status_promo', 'aktif')
                              ->get();

        foreach ($expiredPromos as $promo) {
            $promo->update(['status_promo' => 'tidak aktif']);
        }

        $this->info(count($expiredPromos) . ' promo telah dinonaktifkan.');
    }
}