<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshPriceCache extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'prices:refresh-cache 
                            {--force : Force refresh all prices regardless of last update time}
                            {--user= : Refresh prices only for specific user ID}';

    /**
     * The console command description.
     */
    protected $description = 'Refresh cached prices for user collections and decks from pricing sources';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        $userId = $this->option('user');
        
        $this->info('Starting price cache refresh...');
        
        try {
            // Refresh collection prices
            $this->info('Refreshing collection prices...');
            $collectionUpdated = $this->refreshCollectionPrices($force, $userId);
            $this->info("Updated {$collectionUpdated} collection items");
            
            // Refresh deck prices
            $this->info('Refreshing deck prices...');
            $deckUpdated = $this->refreshDeckPrices($force, $userId);
            $this->info("Updated {$deckUpdated} deck cards");
            
            $this->info('Price cache refresh completed!');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error during price refresh: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
    
    /**
     * Refresh prices for user_collection table using direct SQL updates
     */
    private function refreshCollectionPrices(bool $force, ?int $userId): int
    {
        $updated = 0;
        $now = now();
        
        // Build WHERE conditions
        $whereConditions = [];
        $bindings = [];
        
        if ($userId) {
            $whereConditions[] = 'uc.user_id = ?';
            $bindings[] = $userId;
        }
        
        if (!$force) {
            $whereConditions[] = '(uc.cached_price_updated_at IS NULL OR uc.cached_price_updated_at < ?)';
            $bindings[] = $now->copy()->subHours(12);
        }
        
        $whereClause = !empty($whereConditions) ? 'AND ' . implode(' AND ', $whereConditions) : '';
        
        // Update TCGDEX items - use EUR price
        $tcgdexSql = "
            UPDATE user_collection uc
            JOIN tcgdx_cards tc ON uc.tcgdex_card_id = tc.id
            SET 
                uc.cached_price = tc.price_eur,
                uc.cached_price_currency = 'EUR',
                uc.cached_price_updated_at = ?
            WHERE uc.tcgdex_card_id IS NOT NULL
            AND tc.price_eur IS NOT NULL
            {$whereClause}
        ";
        
        $updated += DB::update($tcgdexSql, array_merge([$now], $bindings));
        
        // Update TCGCSV items - use Cardmarket EUR price
        $tcgcsvSql = "
            UPDATE user_collection uc
            JOIN tcgcsv_products tp ON uc.product_id = tp.product_id
            SET 
                uc.cached_price = tp.cardmarket_price_eur,
                uc.cached_price_currency = 'EUR',
                uc.cached_price_updated_at = ?
            WHERE uc.product_id IS NOT NULL
            AND tp.cardmarket_price_eur IS NOT NULL
            {$whereClause}
        ";
        
        $updated += DB::update($tcgcsvSql, array_merge([$now], $bindings));
        
        return $updated;
    }
    
    /**
     * Refresh prices for deck_cards table using direct SQL updates
     */
    private function refreshDeckPrices(bool $force, ?int $userId): int
    {
        $updated = 0;
        $now = now();
        
        // Build WHERE conditions
        $whereConditions = [];
        $bindings = [];
        
        if ($userId) {
            $whereConditions[] = 'd.user_id = ?';
            $bindings[] = $userId;
        }
        
        if (!$force) {
            $whereConditions[] = '(dc.cached_price_updated_at IS NULL OR dc.cached_price_updated_at < ?)';
            $bindings[] = $now->copy()->subHours(12);
        }
        
        $whereClause = !empty($whereConditions) ? 'AND ' . implode(' AND ', $whereConditions) : '';
        
        // Update TCGDEX deck cards - use EUR price
        $tcgdexSql = "
            UPDATE deck_cards dc
            JOIN decks d ON dc.deck_id = d.id
            JOIN tcgdx_cards tc ON dc.tcgdex_card_id = tc.id
            SET 
                dc.cached_price = tc.price_eur,
                dc.cached_price_currency = 'EUR',
                dc.cached_price_updated_at = ?
            WHERE dc.tcgdex_card_id IS NOT NULL
            AND tc.price_eur IS NOT NULL
            {$whereClause}
        ";
        
        $updated += DB::update($tcgdexSql, array_merge([$now], $bindings));
        
        // Update TCGCSV deck cards - use Cardmarket EUR price
        $tcgcsvSql = "
            UPDATE deck_cards dc
            JOIN decks d ON dc.deck_id = d.id
            JOIN tcgcsv_products tp ON dc.product_id = tp.product_id
            SET 
                dc.cached_price = tp.cardmarket_price_eur,
                dc.cached_price_currency = 'EUR',
                dc.cached_price_updated_at = ?
            WHERE dc.product_id IS NOT NULL
            AND tp.cardmarket_price_eur IS NOT NULL
            {$whereClause}
        ";
        
        $updated += DB::update($tcgcsvSql, array_merge([$now], $bindings));
        
        return $updated;
    }
}
