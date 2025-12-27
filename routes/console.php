<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Scheduled Tasks
Schedule::command('remember:purge-expired')->daily();

// Cardmarket ETL: Run daily at 2:10 AM (Europe/Copenhagen)
Schedule::command('cardmarket:etl --queue')
    ->dailyAt('02:10')
    ->timezone('Europe/Copenhagen')
    ->withoutOverlapping()
    ->onOneServer();

// Artisan Commands
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
