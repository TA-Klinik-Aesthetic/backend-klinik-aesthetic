<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('promo:expire')->everyMinute();

        // $schedule->call(function () {
        //     $cutoff = now()->startOfMonth()->toDateString();
        //     \App\Models\JadwalTreatment::where('tanggal_treatment', '<', $cutoff)->delete();
        // })->monthlyOn(1, '00:00');

        // $nextStart = now()->addMonthNoOverflow()->startOfMonth()->toDateString();
        // $nextEnd   = now()->addMonthNoOverflow()->endOfMonth()->toDateString();
        // $schedule->command("jadwal:generate --start={$nextStart} --end={$nextEnd}")
        //          ->monthlyOn(20, '00:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
