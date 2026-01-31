<?php

namespace App\Console\Commands;

use App\Models\UserCollection;
use App\Models\DeckCard;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     * Refresh prices for user_collection table
     */
    private function refreshCollectionPrices(bool $force, ?int $userId): int
    {
        $query = UserCollection::query();
        
        // Filter by user if specified
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        // Skip recently updated unless force
        if (!$force) {
            $query->where(function($q) {
                $q->whereNull('cached_price_updated_at')
                  ->orWhere('cached_price_updated_at', '<', now()->subHours(12));
            });
        }
        
        // Process in chunks to avoid memory issues
        $updated = 0;
        
        $query->with(['user', 'tcgdexCard'])
            ->chunkById(500, function ($items) use (&$updated) {
                foreach ($items as $item) {
                    try {
                        // Eager load card relations only for TCGCSV items
                        if ($item->product_id) {
                            $item->load('card.prices', 'card.cardmarketProduct.latestPriceQuote');
                        }
                        
                        $price = $this->getPriceForCollectionItem($item);
                        
                        if ($price !== null) {
                            $item->update([
                                'cached_price' => $price['amount'],
                                'cached_price_currency' => $price['currency'],
                                'cached_price_updated_at' => now(),
                            ]);
                            $updated++;
                        }
                    } catch (\Exception $e) {
                        // Log error but continue processing
                        Log::warning('Failed to update price for collection item ' . $item->id . ': ' . $e->getMessage());
                    }
                }
            });
        
        return $updated;
    }
    
    /**
     * Refresh prices for deck_cards table
     */
    private function refreshDeckPrices(bool $force, ?int $userId): int
    {
        $query = DeckCard::query();
        
        // Filter by deck owner if user specified
        if ($userId) {
            $query->whereHas('deck', function($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }
        
        // Skip recently updated unless force
        if (!$force) {
            $query->where(function($q) {
                $q->whereNull('cached_price_updated_at')
                  ->orWhere('cached_price_updated_at', '<', now()->subHours(12));
            });
        }
        
        // Process in chunks
        $updated = 0;
        
        $query->with(['deck.user', 'tcgdexCard'])
            ->chunkById(500, function ($items) use (&$updated) {
                foreach ($items as $item) {
                    try {
                        // Eager load card relations only for TCGCSV items
                        if ($item->product_id) {
                            $item->load('card.prices', 'card.cardmarketProduct.latestPriceQuote');
                        }
                        
                        $price = $this->getPriceForDeckCard($item);
                        
                        if ($price !== null) {
                            $item->update([
                                'cached_price' => $price['amount'],
                                'cached_price_currency' => $price['currency'],
                                'cached_price_updated_at' => now(),
                            ]);
                            $updated++;
                        }
                    } catch (\Exception $e) {
                        // Log error but continue processing
                        Log::warning('Failed to update price for deck card ' . $item->id . ': ' . $e->getMessage());
                    }
                }
            });
        
        return $updated;
    }
    
    /**
     * Get price for a collection item (supports both TCGCSV and TCGDEX)
     */
    private function getPriceForCollectionItem(UserCollection $item): ?array
    {
        // Determine user's preferred currency (default to USD if not set)
        $currency = $item->user->preferred_currency ?? 'USD';
        
        // TCGDEX card
        if ($item->tcgdex_card_id && $item->tcgdexCard) {
            return $this->getTcgdexPrice($item->tcgdexCard, $currency);
        }
        
        // TCGCSV card
        if ($item->product_id && $item->card) {
            return $this->getTcgcsvPrice($item->card, $currency);
        }
        
        return null;
    }
    
    /**
     * Get price for a deck card (supports both TCGCSV and TCGDEX)
     */
    private function getPriceForDeckCard(DeckCard $item): ?array
    {
        // Determine deck owner's preferred currency (default to USD if not set)
        $currency = $item->deck->user->preferred_currency ?? 'USD';
        
        // TCGDEX card
        if ($item->tcgdex_card_id && $item->tcgdexCard) {
            return $this->getTcgdexPrice($item->tcgdexCard, $currency);
        }
        
        // TCGCSV card
        if ($item->product_id && $item->card) {
            return $this->getTcgcsvPrice($item->card, $currency);
        }
        
        return null;
    }
    
    /**
     * Get price from TCGDEX card data
     */
    private function getTcgdexPrice($card, string $currency): ?array
    {
        // Use pre-calculated price columns (populated during import)
        $priceUsd = $card->price_usd;
        $priceEur = $card->price_eur;
        
        // If both null, try fallback to raw field
        if ($priceUsd === null && $priceEur === null) {
            // Legacy fallback for cards without price columns
            $pricing = $card->raw['pricing'] ?? null;
            
            if ($pricing && isset($pricing['cardmarket']['averageSellPrice'])) {
                $priceEur = $pricing['cardmarket']['averageSellPrice'];
            }
        }
        
        // Return price in requested currency
        if ($currency === 'USD') {
            if ($priceUsd !== null) {
                return [
                    'amount' => round($priceUsd, 2),
                    'currency' => 'USD',
                ];
            }
            
            // Convert EUR to USD if USD not available
            if ($priceEur !== null) {
                return [
                    'amount' => round($priceEur * 1.10, 2),
                    'currency' => 'USD',
                ];
            }
        } else {
            // EUR requested
            if ($priceEur !== null) {
                return [
                    'amount' => round($priceEur, 2),
                    'currency' => 'EUR',
                ];
            }
            
            // Convert USD to EUR if EUR not available
            if ($priceUsd !== null) {
                return [
                    'amount' => round($priceUsd / 1.10, 2),
                    'currency' => 'EUR',
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Get price from TCGCSV card data
     */
    private function getTcgcsvPrice($card, string $currency): ?array
    {
        // Try Cardmarket first if available (EUR pricing)
        if ($card->cardmarketProduct && $card->cardmarketProduct->latestPriceQuote) {
            $quote = $card->cardmarketProduct->latestPriceQuote;
            $priceEur = $quote->avg_sell_price ?? $quote->trend_price;
            
            if ($priceEur) {
                if ($currency === 'USD') {
                    // Convert EUR to USD
                    return [
                        'amount' => round($priceEur * 1.10, 2),
                        'currency' => 'USD',
                    ];
                }
                
                return [
                    'amount' => $priceEur,
                    'currency' => 'EUR',
                ];
            }
        }
        
        // Fallback to TCGCSV prices (USD)
        $latestPrice = $card->prices()->latest('snapshot_at')->first();
        
        if ($latestPrice) {
            $priceUsd = $latestPrice->market ?? $latestPrice->mid;
            
            if ($priceUsd) {
                if ($currency === 'EUR') {
                    // Convert USD to EUR
                    return [
                        'amount' => round($priceUsd / 1.10, 2),
                        'currency' => 'EUR',
                    ];
                }
                
                return [
                    'amount' => $priceUsd,
                    'currency' => 'USD',
                ];
            }
        }
        
        return null;
    }
}
