<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Clean up old notifications daily at 2:00 AM
        $schedule->command('notifications:cleanup')
                 ->dailyAt('02:00')
                 ->withoutOverlapping()
                 ->runInBackground();
        
        // Clean up old completed/approved reports monthly at 3:00 AM on the 1st day
        $schedule->command('reports:cleanup --status=completed --status=approved --days=730')
                 ->monthlyOn(1, '03:00')
                 ->withoutOverlapping()
                 ->runInBackground();
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