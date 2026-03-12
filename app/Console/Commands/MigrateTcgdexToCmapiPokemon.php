<?php

namespace App\Console\Commands;

use App\Models\Cmapi\CmapiCard;
use App\Models\Tcgdx\TcgdxCard;
use App\Models\UserCollection;
use App\Models\DeckCard;
use Illuminate\Console\Command;

class MigrateTcgdexToCmapiPokemon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pokemon:migrate-tcgdex-to-cmapi
                            {--dry-run : Preview changes without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Map Pokemon TCGDEX cards in user_collection and deck_cards to CMAPI cards via Cardmarket product ID';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔀 Migrating Pokemon TCGDEX references to CMAPI via Cardmarket product IDs...');
        $this->newLine();

        $dryRun = (bool) $this->option('dry-run');

        $stats = [
            'collection_processed' => 0,
            'collection_mapped' => 0,
            'collection_no_tcgdex' => 0,
            'collection_no_cardmarket' => 0,
            'collection_no_cmapi_match' => 0,
            'deck_processed' => 0,
            'deck_mapped' => 0,
            'deck_no_tcgdex' => 0,
            'deck_no_cardmarket' => 0,
            'deck_no_cmapi_match' => 0,
        ];

        // Helper closure to find CMAPI card for a given TCGDEX card
        $findCmapiForTcgdx = function (TcgdxCard $tcgdxCard): ?CmapiCard {
            $productId = $tcgdxCard->cardmarket_product_id;
            if (! $productId) {
                return null;
            }

            return CmapiCard::where('cardmarket_id', $productId)
                ->where('game', 'pokemon')
                ->first();
        };

        // 1) Migrate user_collection
        $this->info('📂 Processing user_collection (TCGDEX → CMAPI)...');

        UserCollection::whereNotNull('tcgdex_card_id')
            ->whereNull('cmapi_card_id')
            ->chunkById(500, function ($items) use (&$stats, $dryRun, $findCmapiForTcgdx) {
                foreach ($items as $item) {
                    $stats['collection_processed']++;

                    $tcgdx = $item->tcgdexCard;
                    if (! $tcgdx) {
                        $stats['collection_no_tcgdex']++;
                        continue;
                    }

                    if (! $tcgdx->cardmarket_product_id) {
                        $stats['collection_no_cardmarket']++;
                        continue;
                    }

                    $cmapi = $findCmapiForTcgdx($tcgdx);
                    if (! $cmapi) {
                        $stats['collection_no_cmapi_match']++;
                        continue;
                    }

                    if (! $dryRun) {
                        $item->cmapi_card_id = $cmapi->cmapi_id;
                        $item->save();
                    }

                    $stats['collection_mapped']++;
                }
            });

        $this->newLine();

        // 2) Migrate deck_cards
        $this->info('🧩 Processing deck_cards (TCGDEX → CMAPI)...');

        DeckCard::whereNotNull('tcgdex_card_id')
            ->whereNull('cmapi_card_id')
            ->chunkById(500, function ($items) use (&$stats, $dryRun, $findCmapiForTcgdx) {
                foreach ($items as $deckCard) {
                    $stats['deck_processed']++;

                    $tcgdx = $deckCard->tcgdexCard;
                    if (! $tcgdx) {
                        $stats['deck_no_tcgdex']++;
                        continue;
                    }

                    if (! $tcgdx->cardmarket_product_id) {
                        $stats['deck_no_cardmarket']++;
                        continue;
                    }

                    $cmapi = $findCmapiForTcgdx($tcgdx);
                    if (! $cmapi) {
                        $stats['deck_no_cmapi_match']++;
                        continue;
                    }

                    if (! $dryRun) {
                        $deckCard->cmapi_card_id = $cmapi->cmapi_id;
                        $deckCard->save();
                    }

                    $stats['deck_mapped']++;
                }
            });

        $this->newLine(2);

        // Summary table
        $this->info('✅ Migration summary');
        $this->newLine();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Collection rows processed', $stats['collection_processed']],
                ['Collection mapped', $stats['collection_mapped']],
                ['Collection: no tcgdexCard relation', $stats['collection_no_tcgdex']],
                ['Collection: no cardmarket_product_id on TCGDEX card', $stats['collection_no_cardmarket']],
                ['Collection: no CMAPI match for Cardmarket ID', $stats['collection_no_cmapi_match']],
                ['Deck rows processed', $stats['deck_processed']],
                ['Deck mapped', $stats['deck_mapped']],
                ['Deck: no tcgdexCard relation', $stats['deck_no_tcgdex']],
                ['Deck: no cardmarket_product_id on TCGDEX card', $stats['deck_no_cardmarket']],
                ['Deck: no CMAPI match for Cardmarket ID', $stats['deck_no_cmapi_match']],
            ]
        );

        if ($dryRun) {
            $this->warn('🔍 DRY RUN: no changes were written to the database.');
        }

        $this->newLine();

        return Command::SUCCESS;
    }
}

