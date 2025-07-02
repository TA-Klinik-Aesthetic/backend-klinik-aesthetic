<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JadwalTreatment;
use App\Models\DetailJadwalTreatment;
use Carbon\Carbon;

class GenerateMonthlyJadwalTreatment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jadwal:generate 
        {--start= : tanggal mulai (YYYY-MM-DD)} 
        {--end=   : tanggal akhir}';

    protected $description = 'Generate slot jadwal treatment per hari antara start-end (default: bulan ini)';

    public function handle()
    {
        // jika user tidak kasih --start, pakai awal bulan ini
        $start = $this->option('start')
            ? Carbon::parse($this->option('start'))
            : Carbon::now()->startOfMonth();

        // jika user tidak kasih --end, pakai akhir bulan ini
        $end = $this->option('end')
            ? Carbon::parse($this->option('end'))
            : Carbon::now()->endOfMonth();

        // definisi jam kerja yang mau di-generate
        $times = ['09:00','10:00','11:00', '12:00', '13:00','15:00','17:00','19:00'];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            // 1) Master tanggal
            $jt = JadwalTreatment::firstOrCreate([
                'tanggal_treatment' => $date->toDateString(),
            ]);

            // 2) Detail per jam
            foreach ($times as $t) {
                DetailJadwalTreatment::firstOrCreate(
                    ['id_jadwal_treatment' => $jt->id_jadwal_treatment, 'waktu_tersedia' => $t],
                    ['maks_booking' => 2, 'status_jadwal' => 'tersedia']
                );
            }
        }

        $this->info("Jadwal treatment ter-generate: {$start->toDateString()} ⇢ {$end->toDateString()}");
    }
}
