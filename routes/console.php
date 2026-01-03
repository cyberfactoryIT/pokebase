<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Scheduled Tasks
Schedule::command('remember:purge-expired')->daily();

// Cardmarket ETL: Run daily at 2:10 AM (Europe/Copenhagen)
Schedule::command('cardmarket:etl')
    ->dailyAt('02:10')
    ->timezone('Europe/Copenhagen')
    ->withoutOverlapping()
    ->onOneServer();

// TCGCSV Import Pokemon: Run daily at 2:40 AM (Europe/Copenhagen)
Schedule::command('tcgcsv:import-pokemon')
    ->dailyAt('02:40')
    ->timezone('Europe/Copenhagen')
    ->withoutOverlapping()
    ->onOneServer();

// RapidAPI Import Episodes: Run daily at 3:30 AM (Europe/Copenhagen)
// Imports episode list (metadata only, ~172 episodes)
Schedule::command('rapidapi:import-episodes pokemon')
    ->dailyAt('03:30')
    ->timezone('Europe/Copenhagen')
    ->withoutOverlapping()
    ->onOneServer();

// RapidAPI Sync Cards: Run daily at 3:35 AM after episodes import (Europe/Copenhagen)
// Syncs ALL episode cards with daily price snapshots (~171 episodes, ~513 API calls, 8-10 min duration with 300 req/min limit)
Schedule::command('rapidapi:sync-cards pokemon')
    ->dailyAt('03:35')
    ->timezone('Europe/Copenhagen')
    ->withoutOverlapping()
    ->onOneServer();

// TCGdex Import: Run daily at 4:45 AM after RapidAPI sync (Europe/Copenhagen)
Schedule::command('tcgdx:import')
    ->dailyAt('04:45')
    ->timezone('Europe/Copenhagen')
    ->withoutOverlapping()
    ->onOneServer();

// RapidAPI Episodes Mapping to TCGCSV: Run daily at 5:30 AM (Europe/Copenhagen)
Schedule::command('rapidapi:map-episodes')
    ->dailyAt('05:30')
    ->timezone('Europe/Copenhagen')
    ->withoutOverlapping()
    ->onOneServer();

// TCGdex to TCGCSV Mapping: Run daily at 5:50 AM (Europe/Copenhagen)
Schedule::command('tcgdex:map')
    ->dailyAt('05:50')
    ->timezone('Europe/Copenhagen')
    ->withoutOverlapping()
    ->onOneServer();

// TCGCSV Enrichment with RapidAPI and Cardmarket data: Run daily at 6:00 AM (Europe/Copenhagen)
Schedule::command('tcgcsv:enrich --all')
    ->dailyAt('06:00')
    ->timezone('Europe/Copenhagen')
    ->withoutOverlapping()
    ->onOneServer();

// Artisan Commands
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
