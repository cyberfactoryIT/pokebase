<?php
namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule($schedule)
    {
        $schedule->command('remember:purge-expired')->daily();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}
