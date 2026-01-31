<?php
namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule($schedule)
    {
        $schedule->command('remember:purge-expired')->daily();
        $schedule->command('deck-evaluation:mark-expired')->hourly();
        
        // Refresh price cache twice daily (after ETL updates)
        $schedule->command('prices:refresh-cache')
            ->twiceDaily(6, 18) // 6 AM and 6 PM
            ->withoutOverlapping()
            ->runInBackground();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}
