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
        
        // Update TCGDEX items
        $tcgdexSql = "
            UPDATE user_collection uc
            JOIN tcgdx_cards tc ON uc.tcgdex_card_id = tc.id
            JOIN users u ON uc.user_id = u.id
            SET 
                uc.cached_price = CASE 
                    WHEN u.preferred_currency = 'USD' THEN tc.price_usd
                    ELSE COALESCE(tc.price_eur, tc.price_usd)
                END,
                uc.cached_price_currency = CASE 
                    WHEN u.preferred_currency = 'USD' AND tc.price_usd IS NOT NULL THEN 'USD'
                    WHEN tc.price_eur IS NOT NULL THEN 'EUR'
                    WHEN tc.price_usd IS NOT NULL THEN 'USD'
                    ELSE COALESCE(u.preferred_currency, 'USD')
                END,
                uc.cached_price_updated_at = ?
            WHERE uc.tcgdex_card_id IS NOT NULL
            AND (tc.price_usd IS NOT NULL OR tc.price_eur IS NOT NULL)
            {$whereClause}
        ";
        
        $updated += DB::update($tcgdexSql, array_merge([$now], $bindings));
        
        // Update TCGCSV items  
        $tcgcsvSql = "
            UPDATE user_collection uc
            JOIN tcgcsv_products tp ON uc.product_id = tp.product_id
            JOIN users u ON uc.user_id = u.id
            SET 
                uc.cached_price = CASE 
                    WHEN u.preferred_currency = 'USD' THEN tp.price_usd
                    ELSE COALESCE(tp.cardmarket_price_eur, tp.price_usd)
                END,
                uc.cached_price_currency = CASE 
                    WHEN u.preferred_currency = 'USD' AND tp.price_usd IS NOT NULL THEN 'USD'
                    WHEN tp.cardmarket_price_eur IS NOT NULL THEN 'EUR'
                    WHEN tp.price_usd IS NOT NULL THEN 'USD'
                    ELSE COALESCE(u.preferred_currency, 'USD')
                END,
                uc.cached_price_updated_at = ?
            WHERE uc.product_id IS NOT NULL
            AND (tp.price_usd IS NOT NULL OR tp.cardmarket_price_eur IS NOT NULL)
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
        
        // Update TCGDEX deck cards
        $tcgdexSql = "
            UPDATE deck_cards dc
            JOIN decks d ON dc.deck_id = d.id
            JOIN tcgdx_cards tc ON dc.tcgdex_card_id = tc.id
            JOIN users u ON d.user_id = u.id
            SET 
                dc.cached_price = CASE 
                    WHEN u.preferred_currency = 'USD' THEN tc.price_usd
                    ELSE COALESCE(tc.price_eur, tc.price_usd)
                END,
                dc.cached_price_currency = CASE 
                    WHEN u.preferred_currency = 'USD' AND tc.price_usd IS NOT NULL THEN 'USD'
                    WHEN tc.price_eur IS NOT NULL THEN 'EUR'
                    WHEN tc.price_usd IS NOT NULL THEN 'USD'
                    ELSE COALESCE(u.preferred_currency, 'USD')
                END,
                dc.cached_price_updated_at = ?
            WHERE dc.tcgdex_card_id IS NOT NULL
            AND (tc.price_usd IS NOT NULL OR tc.price_eur IS NOT NULL)
            {$whereClause}
        ";
        
        $updated += DB::update($tcgdexSql, array_merge([$now], $bindings));
        
        // Update TCGCSV deck cards
        $tcgcsvSql = "
            UPDATE deck_cards dc
            JOIN decks d ON dc.deck_id = d.id
            JOIN tcgcsv_products tp ON dc.product_id = tp.product_id
            JOIN users u ON d.user_id = u.id
            SET 
                dc.cached_price = CASE 
                    WHEN COALESCE(u.preferred_currency, 'USD') = 'USD' THEN tp.price_usd
                    ELSE tp.cardmarket_price_eur
                END,
                dc.cached_price_currency = COALESCE(u.preferred_currency, 'USD'),
                dc.cached_price_updated_at = ?
            WHERE dc.product_id IS NOT NULL
            AND (
                (COALESCE(u.preferred_currency, 'USD') = 'USD' AND tp.price_usd IS NOT NULL) OR
                (COALESCE(u.preferred_currency, 'USD') = 'EUR' AND tp.cardmarket_price_eur IS NOT NULL)
            )
            {$whereClause}
        ";
        
        $updated += DB::update($tcgcsvSql, array_merge([$now], $bindings));
        
        return $updated;
    }
}
