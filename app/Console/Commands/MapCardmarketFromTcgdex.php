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
    protected $description = 'Map CardMarket product IDs from TCGdex data to TCGCSV products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔗 Mapping CardMarket IDs from TCGdex to TCGCSV products...');
        $this->newLine();
        
        $dryRun = $this->option('dry-run');
        
        // Get all tcgcsv products that have a tcgdex mapping but no cardmarket_product_id
        $products = TcgcsvProduct::whereNotNull('tcgdex_card_id')
            ->whereNull('cardmarket_product_id')
            ->with('tcgdxCard')
            ->get();
        
        $this->info("Found {$products->count()} products with TCGdex mapping but no CardMarket ID");
        $this->newLine();
        
        $stats = [
            'mapped' => 0,
            'skipped_no_tcgdex' => 0,
            'skipped_no_id' => 0,
            'errors' => 0,
        ];
        
        $progressBar = $this->output->createProgressBar($products->count());
        $progressBar->start();
        
        foreach ($products as $product) {
            try {
                // Get the tcgdex card
                $tcgdexCard = $product->tcgdxCard;
                
                if (!$tcgdexCard) {
                    $stats['skipped_no_tcgdex']++;
                    $progressBar->advance();
                    continue;
                }
                
                // Extract CardMarket ID from raw JSON
                $raw = $tcgdexCard->raw;
                $cardmarketId = $raw['pricing']['cardmarket']['idProduct'] ?? null;
                
                if (!$cardmarketId) {
                    $stats['skipped_no_id']++;
                    $progressBar->advance();
                    continue;
                }
                
                // Update the product
                if (!$dryRun) {
                    $product->cardmarket_product_id = $cardmarketId;
                    $product->save();
                }
                
                $stats['mapped']++;
                
            } catch (\Exception $e) {
                $stats['errors']++;
                $this->newLine();
                $this->error("Error processing product {$product->product_id}: {$e->getMessage()}");
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
                ['Mapped', $stats['mapped']],
                ['Skipped (no TCGdex card)', $stats['skipped_no_tcgdex']],
                ['Skipped (no CardMarket ID)', $stats['skipped_no_id']],
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
