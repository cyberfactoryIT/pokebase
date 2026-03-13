<?php

namespace App\Console\Commands;

use App\Models\Cmapi\CmapiSet;
use Illuminate\Console\Command;

class CmapiBackfillSetCardCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmapi:backfill-set-card-count {--game=* : Limit to one or more games (e.g. lorcana,onepiece,pokemon)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill cmapi_sets.card_count from raw JSON (cards_printed_total / total_cards)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $games = (array) $this->option('game');
        $games = array_filter($games);

        $this->info('🔁 Backfilling cmapi_sets.card_count from raw JSON...');

        $query = CmapiSet::query()
            ->whereNull('card_count');

        if (!empty($games)) {
            $query->whereIn('game', $games);
            $this->info('Limiting to games: ' . implode(', ', $games));
        }

        $updated = 0;

        $query->chunkById(100, function ($sets) use (&$updated) {
            foreach ($sets as $set) {
                $raw = $set->raw ?? [];

                // Prefer cards_printed_total, fallback to total_cards
                $cardCount = null;
                if (is_array($raw)) {
                    if (isset($raw['cards_printed_total']) && is_numeric($raw['cards_printed_total'])) {
                        $cardCount = (int) $raw['cards_printed_total'];
                    } elseif (isset($raw['total_cards']) && is_numeric($raw['total_cards'])) {
                        $cardCount = (int) $raw['total_cards'];
                    }
                }

                if ($cardCount !== null && $cardCount > 0) {
                    $set->card_count = $cardCount;
                    $set->save();
                    $updated++;
                }
            }
        });

        $this->info("✅ Backfill completed. Updated {$updated} sets.");

        return Command::SUCCESS;
    }
}

