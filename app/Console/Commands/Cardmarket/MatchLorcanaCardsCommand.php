<?php

namespace App\Console\Commands\Cardmarket;

use App\Models\Cmapi\CmapiCard;
use App\Models\CardmarketProductLorcana;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MatchLorcanaCardsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cardmarket:match-lorcana 
                            {--force : Force rematch all cards}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Match Lorcana cards from RapidAPI (cmapi_cards) to CardMarket products (cardmarket_products_lorcana)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');

        $this->info('🔗 Matching Lorcana Cards to CardMarket Products');
        $this->newLine();

        try {
            // Get Lorcana cards from RapidAPI
            $query = CmapiCard::where('game', 'lorcana')
                ->whereNotNull('cardmarket_id');
            
            if (!$force) {
                // Only match cards that don't have a linked CardMarket product yet
                $query->whereDoesntHave('cardmarketProductLorcana');
            }
            
            $lorcanaCards = $query->get();
            
            $this->info("Found {$lorcanaCards->count()} Lorcana cards to match");
            $this->newLine();

            $matched = 0;
            $notFound = 0;
            $progressBar = $this->output->createProgressBar($lorcanaCards->count());

            foreach ($lorcanaCards as $card) {
                // Find matching CardMarket product by cardmarket_id
                $cardmarketProduct = CardmarketProductLorcana::where('cardmarket_product_id', $card->cardmarket_id)->first();
                
                if ($cardmarketProduct) {
                    // Update the link
                    $cardmarketProduct->update(['cmapi_card_id' => $card->id]);
                    $matched++;
                } else {
                    $notFound++;
                }
                
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            // Display results
            $this->info('✅ Matching completed!');
            $this->table(
                ['Status', 'Count'],
                [
                    ['Matched', $matched],
                    ['Not Found in CardMarket', $notFound],
                    ['Total Processed', $lorcanaCards->count()],
                ]
            );

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Matching failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
