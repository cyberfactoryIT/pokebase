<?php

namespace App\Console\Commands;

use App\Models\UserCollection;
use App\Models\DeckCard;
use App\Models\Tcgdx\TcgdxCard;
use App\Models\Cmapi\CmapiCard;
use App\Models\TcgcsvProduct;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshCachedPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prices:refresh {--force : Force update even if recently updated}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh cached prices for all cards in collections and decks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting price refresh...');
        
        $force = $this->option('force');
        
        // Refresh Collection Prices
        $this->info('Updating UserCollection prices...');
        $collectionUpdated = $this->refreshCollectionPrices($force);
        $this->info("✓ Updated {$collectionUpdated} collection entries");
        
        // Refresh Deck Card Prices
        $this->info('Updating DeckCard prices...');
        $deckUpdated = $this->refreshDeckPrices($force);
        $this->info("✓ Updated {$deckUpdated} deck card entries");
        
        $this->info("Done! Total updated: " . ($collectionUpdated + $deckUpdated));
        
        return 0;
    }
    
    private function refreshCollectionPrices($force = false)
    {
        $updated = 0;
        
        // TCGDEX Cards
        $tcgdexItems = UserCollection::whereNotNull('tcgdex_card_id')
            ->with('tcgdexCard')
            ->get();
        
        foreach ($tcgdexItems as $item) {
            if (!$item->tcgdexCard) continue;
            
            $card = $item->tcgdexCard;
            $price = null;
            $currency = 'USD';
            
            if ($card->price_eur && $card->price_eur > 0) {
                $price = $card->price_eur;
                $currency = 'EUR';
            } elseif ($card->price_usd && $card->price_usd > 0) {
                $price = $card->price_usd;
                $currency = 'USD';
            }
            
            if ($price !== null) {
                $item->cached_price = $price;
                $item->cached_price_currency = $currency;
                $item->cached_price_updated_at = now();
                $item->save();
                $updated++;
            }
        }
        
        // CMAPI Cards
        $cmapiItems = UserCollection::whereNotNull('cmapi_card_id')
            ->with('cmapiCard')
            ->get();
        
        foreach ($cmapiItems as $item) {
            if (!$item->cmapiCard) continue;
            
            $card = $item->cmapiCard;
            
            if ($card->price_eur && $card->price_eur > 0) {
                $item->cached_price = $card->price_eur;
                $item->cached_price_currency = 'EUR';
                $item->cached_price_updated_at = now();
                $item->save();
                $updated++;
            }
        }
        
        // TCGCSV Products
        $tcgcsvItems = UserCollection::whereNotNull('product_id')
            ->with('product.prices')
            ->get();
        
        foreach ($tcgcsvItems as $item) {
            if (!$item->product) continue;
            
            $latestPrice = $item->product->prices()->orderBy('updated_at', 'desc')->first();
            
            if ($latestPrice && $latestPrice->market_price) {
                $item->cached_price = $latestPrice->market_price;
                $item->cached_price_currency = 'USD';
                $item->cached_price_updated_at = now();
                $item->save();
                $updated++;
            }
        }
        
        return $updated;
    }
    
    private function refreshDeckPrices($force = false)
    {
        $updated = 0;
        
        // TCGDEX Cards in Decks
        $tcgdexDeckCards = DeckCard::whereNotNull('tcgdex_card_id')
            ->with('tcgdexCard')
            ->get();
        
        foreach ($tcgdexDeckCards as $deckCard) {
            if (!$deckCard->tcgdexCard) continue;
            
            $card = $deckCard->tcgdexCard;
            $price = null;
            $currency = 'USD';
            
            if ($card->price_eur && $card->price_eur > 0) {
                $price = $card->price_eur;
                $currency = 'EUR';
            } elseif ($card->price_usd && $card->price_usd > 0) {
                $price = $card->price_usd;
                $currency = 'USD';
            }
            
            if ($price !== null) {
                $deckCard->cached_price = $price;
                $deckCard->cached_price_currency = $currency;
                $deckCard->cached_price_updated_at = now();
                $deckCard->save();
                $updated++;
            }
        }
        
        // CMAPI Cards in Decks
        $cmapiDeckCards = DeckCard::whereNotNull('cmapi_card_id')
            ->with('cmapiCard')
            ->get();
        
        foreach ($cmapiDeckCards as $deckCard) {
            if (!$deckCard->cmapiCard) continue;
            
            $card = $deckCard->cmapiCard;
            
            if ($card->price_eur && $card->price_eur > 0) {
                $deckCard->cached_price = $card->price_eur;
                $deckCard->cached_price_currency = 'EUR';
                $deckCard->cached_price_updated_at = now();
                $deckCard->save();
                $updated++;
            }
        }
        
        // TCGCSV Products in Decks
        $tcgcsvDeckCards = DeckCard::whereNotNull('product_id')
            ->with('product.prices')
            ->get();
        
        foreach ($tcgcsvDeckCards as $deckCard) {
            if (!$deckCard->product) continue;
            
            $latestPrice = $deckCard->product->prices()->orderBy('updated_at', 'desc')->first();
            
            if ($latestPrice && $latestPrice->market_price) {
                $deckCard->cached_price = $latestPrice->market_price;
                $deckCard->cached_price_currency = 'USD';
                $deckCard->cached_price_updated_at = now();
                $deckCard->save();
                $updated++;
            }
        }
        
        return $updated;
    }
}
