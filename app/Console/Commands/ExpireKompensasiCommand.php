<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KompensasiDiberikan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpireKompensasiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kompensasi:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menandai kompensasi yang sudah lewat tanggal berakhirnya menjadi Sudah Kadaluwarsa';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        Log::info("[kompensasi:expire] running at {$now}");

        $expired = KompensasiDiberikan::whereNotNull('tanggal_berakhir_kompensasi')
            ->where('tanggal_berakhir_kompensasi', '<=', $now)
            ->where('status_kompensasi', 'Belum Digunakan') 
            ->get();

        foreach ($expired as $k) {
            $k->update(['status_kompensasi' => 'Sudah Kadaluwarsa']);
            Log::info("[kompensasi:expire] kompensasi id {$k->id_kompensasi_diberikan} kadaluarsa");
        }

        $this->info(count($expired) . ' kompensasi telah ditandai Kadaluarsa.');

        Log::info(sprintf(
            "[kompensasi:expire] selesai, total %d kompensasi ditandai kadaluarsa",
            $expired->count()
        ));
    }
}
