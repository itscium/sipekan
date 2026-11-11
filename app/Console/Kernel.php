<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Tarik log absensi dari mesin fingerprint setiap hari kerja (Senin–Jumat) pukul 09.00 WIB.
        // Butuh cron di server: * * * * * cd /path/ke/sipekan && php artisan schedule:run >> /dev/null 2>&1
        $schedule->command('fingerprint:sync-logs --trigger=otomatis')
            ->weekdays()
            ->at('09:00')
            ->timezone('Asia/Jakarta')
            ->withoutOverlapping(30)
            ->appendOutputTo(storage_path('logs/fingerprint-sync.log'));

        // Hapus log aktivitas pengguna yang lebih dari 90 hari, setiap hari pukul 02.00 WIB.
        $schedule->command('activity:prune --days=90')
            ->dailyAt('02:00')
            ->timezone('Asia/Jakarta')
            ->withoutOverlapping(60);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
