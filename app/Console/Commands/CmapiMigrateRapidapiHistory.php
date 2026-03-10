<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CmapiMigrateRapidapiHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmapi:migrate-rapidapi-history
                            {game : Game code, e.g. pokemon}
                            {--from= : Start date (YYYY-MM-DD)}
                            {--to= : End date (YYYY-MM-DD)}
                            {--dry-run : Only show counts, do not write}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate historical prices from rapidapi_price_history to cmapi_price_history using matching cmapi_id/card_id';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $game = $this->argument('game');
        $from = $this->option('from');
        $to = $this->option('to');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Migrating RapidAPI price history to CMAPI for game [{$game}]");
        if ($dryRun) {
            $this->warn('Running in DRY-RUN mode (no writes will be performed).');
        }

        // Basic date filters
        $query = DB::table('rapidapi_price_history')
            ->where('game', $game);

        if ($from) {
            $query->whereDate('snapshot_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('snapshot_date', '<=', $to);
        }

        $total = $query->count();
        if ($total === 0) {
            $this->info('No rapidapi_price_history rows found for the given filters.');
            return 0;
        }

        $this->info("Found {$total} RapidAPI history rows to examine.");

        $perPage = 1000;
        $processed = 0;
        $inserted = 0;
        $skippedNoCard = 0;

        // Process in chunks to avoid memory issues
        $query->orderBy('snapshot_date')
            ->orderBy('card_id')
            ->chunk($perPage, function ($rows) use (&$processed, &$inserted, &$skippedNoCard, $dryRun, $game) {
                // Preload matching CMAPI cards for this chunk
                $cardIds = $rows->pluck('card_id')->unique()->values()->all();

                $cmapiCards = DB::table('cmapi_cards')
                    ->whereIn('cmapi_id', $cardIds)
                    ->pluck('id', 'cmapi_id'); // [cmapi_id => id]

                foreach ($rows as $row) {
                    $processed++;

                    $cmapiCardId = $cmapiCards[$row->card_id] ?? null;
                    if (!$cmapiCardId) {
                        $skippedNoCard++;
                        continue;
                    }

                    // Derive main EUR price and trend from RapidAPI data
                    $priceEur = $row->cardmarket_low
                        ?? $row->cardmarket_avg
                        ?? $row->cardmarket_trend
                        ?? 0;

                    $priceTrendEur = $row->cardmarket_trend ?? null;

                    if ($dryRun) {
                        // In dry-run we only count potential inserts
                        $inserted++;
                        continue;
                    }

                    try {
                        DB::table('cmapi_price_history')->updateOrInsert(
                            [
                                'cmapi_card_id' => $cmapiCardId,
                                'price_date' => $row->snapshot_date,
                                // We deliberately do NOT include language/condition here
                            ],
                            [
                                'cardmarket_id' => null, // Unknown from RapidAPI, can be filled later if needed
                                'language' => 'en',
                                'condition' => 'NM',
                                'price_eur' => $priceEur,
                                'price_trend_eur' => $priceTrendEur,
                                'available_items' => null,
                                // Store original raw_data + basic metrics as JSON in prices field
                                'prices' => json_encode([
                                    'source' => 'rapidapi',
                                    'cardmarket_avg' => $row->cardmarket_avg,
                                    'cardmarket_low' => $row->cardmarket_low,
                                    'cardmarket_high' => $row->cardmarket_high,
                                    'cardmarket_trend' => $row->cardmarket_trend,
                                    'tcgplayer_market' => $row->tcgplayer_market,
                                    'tcgplayer_low' => $row->tcgplayer_low,
                                    'tcgplayer_high' => $row->tcgplayer_high,
                                    'tcgplayer_mid' => $row->tcgplayer_mid,
                                    'raw_data' => json_decode($row->raw_data, true),
                                ]),
                                'updated_at' => now(),
                                'created_at' => now(),
                            ]
                        );
                        $inserted++;
                    } catch (\Throwable $e) {
                        Log::error('cmapi:migrate-rapidapi-history failed for row', [
                            'game' => $game,
                            'card_id' => $row->card_id,
                            'snapshot_date' => $row->snapshot_date,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                $this->info("Processed {$processed} / potential inserts so far: {$inserted}, skipped (no cmapi_card): {$skippedNoCard}");
            });

        $this->info('Migration finished.');
        $this->info("Total processed: {$processed}");
        $this->info("Total inserted/updated (including dry-run simulated): {$inserted}");
        $this->info("Total skipped (no matching cmapi_card): {$skippedNoCard}");

        return 0;
    }
}
