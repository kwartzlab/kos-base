<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('process:userstatus')->dailyAt('1:00')->withoutOverlapping(10)->appendOutputTo(storage_path('logs/userstatusupdates.log'));
        $schedule->command('generate:recentmembersemail')->monthlyOn(1, '00:05')->withoutOverlapping(10);
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
        $this->load(__DIR__.'/Commands');
    }
}
