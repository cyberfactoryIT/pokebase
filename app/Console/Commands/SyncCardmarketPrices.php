<?php

namespace App\Console\Commands;

use App\Models\TcgcsvProduct;
use App\Models\CardmarketPriceQuote;
use Illuminate\Console\Command;

class SyncCardmarketPrices extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cardmarket:sync-prices 
                            {--limit= : Limit number of products to process}
                            {--force : Force update even if price exists}';

    /**
     * The console command description.
     */
    protected $description = 'Sync Cardmarket trend prices from cardmarket_price_quotes to tcgcsv_products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $force = $this->option('force');
        
        $this->info('Starting Cardmarket price sync...');
        
        // Count total products first
        $query = TcgcsvProduct::whereHas('rapidapiCard', function($q) {
            $q->whereNotNull('cardmarket_id');
        });
        
        if (!$force) {
            $query->whereNull('cardmarket_price_eur');
        }
        
        $total = $limit ? min($query->count(), $limit) : $query->count();
        
        $this->info("Found {$total} products to process");
        
        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();
        
        $updated = 0;
        $skipped = 0;
        $notFound = 0;
        $processed = 0;
        
        // Process in chunks to avoid memory issues
        TcgcsvProduct::with('rapidapiCard')
            ->whereHas('rapidapiCard', function($q) {
                $q->whereNotNull('cardmarket_id');
            })
            ->when(!$force, function($q) {
                $q->whereNull('cardmarket_price_eur');
            })
            ->when($limit, function($q) use ($limit) {
                $q->limit($limit);
            })
            ->chunk(100, function($products) use (&$updated, &$skipped, &$notFound, &$processed, $progressBar, $limit) {
                foreach ($products as $product) {
                    if ($limit && $processed >= $limit) {
                        return false; // Stop chunking
                    }
                    
                    $cardmarketId = $product->rapidapiCard->cardmarket_id ?? null;
                    
                    if (!$cardmarketId) {
                        $skipped++;
                        $processed++;
                        $progressBar->advance();
                        continue;
                    }
                    
                    // Get latest price quote
                    $quote = CardmarketPriceQuote::where('cardmarket_product_id', $cardmarketId)
                        ->latest('as_of_date')
                        ->first();
                    
                    if (!$quote) {
                        $notFound++;
                        $processed++;
                        $progressBar->advance();
                        continue;
                    }
                    
                    // Check if trend > 0
                    if ($quote->trend && $quote->trend > 0) {
                        $product->cardmarket_price_eur = $quote->trend;
                        $product->cardmarket_price_updated_at = $quote->as_of_date;
                        $product->save();
                        $updated++;
                    } else {
                        $skipped++;
                    }
                    
                    $processed++;
                    $progressBar->advance();
                }
            });
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("Sync completed!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Updated', $updated],
                ['Skipped (no trend)', $skipped],
                ['Not found in quotes', $notFound],
                ['Total processed', $processed],
            ]
        );
        
        return Command::SUCCESS;
    }
}
