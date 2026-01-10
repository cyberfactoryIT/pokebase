<?php

namespace App\Console\Commands;

use App\Models\Tcgdx\TcgdxCard;
use App\Models\TcgcsvProduct;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MapCardmarketFromTcgdex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tcgdex:map-cardmarket 
                            {--dry-run : Preview changes without saving}
                            {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Map CardMarket product IDs from TCGdex data to TCGCSV products cardmarket_product_id column';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔗 Mapping CardMarket IDs from TCGdex to TCGCSV products (cardmarket_product_id column)...');
        $this->newLine();
        
        $dryRun = $this->option('dry-run');
        
        // Get all TCGdex cards
        $tcgdexCards = TcgdxCard::all();
        
        $this->info("Found {$tcgdexCards->count()} TCGdex cards to process");
        $this->newLine();
        
        $stats = [
            'mapped' => 0,
            'skipped_no_cardmarket_id' => 0,
            'skipped_no_products' => 0,
            'errors' => 0,
        ];
        
        $progressBar = $this->output->createProgressBar($tcgdexCards->count());
        $progressBar->start();
        
        foreach ($tcgdexCards as $tcgdexCard) {
            try {
                // Extract CardMarket idProduct from raw JSON
                $raw = $tcgdexCard->raw;
                $cardmarketProductId = $raw['pricing']['cardmarket']['idProduct'] ?? null;
                
                if (!$cardmarketProductId) {
                    $stats['skipped_no_cardmarket_id']++;
                    $progressBar->advance();
                    continue;
                }
                
                // Find all TCGCSV products mapped to this TCGdex card
                $products = TcgcsvProduct::where('tcgdex_card_id', $tcgdexCard->tcgdex_id)
                    ->whereNull('cardmarket_product_id')
                    ->get();
                
                if ($products->isEmpty()) {
                    $stats['skipped_no_products']++;
                    $progressBar->advance();
                    continue;
                }
                
                // Update all matching products
                if (!$dryRun) {
                    TcgcsvProduct::where('tcgdex_card_id', $tcgdexCard->tcgdex_id)
                        ->whereNull('cardmarket_product_id')
                        ->update(['cardmarket_product_id' => $cardmarketProductId]);
                }
                
                $stats['mapped'] += $products->count();
                
            } catch (\Exception $e) {
                $stats['errors']++;
                $this->newLine();
                $this->error("Error processing TCGdex card {$tcgdexCard->tcgdex_id}: {$e->getMessage()}");
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        // Display summary
        $this->info('✅ Mapping completed!');
        $this->newLine();
        
        $this->table(
            ['Status', 'Count'],
            [
                ['Products Mapped', $stats['mapped']],
                ['TCGdex cards without CardMarket ID', $stats['skipped_no_cardmarket_id']],
                ['TCGdex cards without products', $stats['skipped_no_products']],
                ['Errors', $stats['errors']],
            ]
        );
        
        if ($dryRun) {
            $this->warn('🔍 DRY RUN: No changes were saved');
        }
        
        $this->newLine();
        
        return Command::SUCCESS;
    }
}
