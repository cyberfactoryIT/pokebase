<?php

namespace App\Http\Controllers\Pokemon;

use App\Http\Controllers\Controller;
use App\Models\Tcgdx\TcgdxSet;
use App\Models\Tcgdx\TcgdxCard;
use App\Models\TcgcsvGroup;
use App\Models\TcgcsvProduct;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Display list of sets
     */
    public function sets(Request $request): View
    {
        $currentGame = $request->attributes->get('currentGame');
        
        // If no current game (user not logged in), default to Pokemon
        if (!$currentGame) {
            $currentGame = \App\Models\Game::where('code', 'pokemon')->first();
            if (!$currentGame) {
                abort(404, 'Pokemon game not found');
            }
        }
        
        // Pokemon routes ALWAYS use TCGDEX when configured
        if (config('catalog.backend') === 'tcgdex') {
            $sets = TcgdxSet::where('game_id', $currentGame->id)
                ->orderByDesc('release_date')
                ->paginate(24);
            
            return view('pokemon.catalog.sets-tcgdex', [
                'sets' => $sets,
                'currentGame' => $currentGame,
                'backend' => 'tcgdex',
            ]);
        }
        
        // Use TCGCSV data (default)
        $sets = TcgcsvGroup::where('game_id', $currentGame->id)
            ->orderByDesc('published_on')
            ->paginate(24);
        
        return view('pokemon.catalog.sets', [
            'sets' => $sets,
            'currentGame' => $currentGame,
            'backend' => 'tcgcsv',
        ]);
    }
    
    /**
     * Display cards in a set
     */
    public function setCards(Request $request, string $setId): View
    {
        $currentGame = $request->attributes->get('currentGame');
        
        // If no current game (user not logged in), default to Pokemon
        if (!$currentGame) {
            $currentGame = \App\Models\Game::where('code', 'pokemon')->first();
            if (!$currentGame) {
                abort(404, 'Pokemon game not found');
            }
        }
        
        // Pokemon routes ALWAYS use TCGDEX when configured
        if (config('catalog.backend') === 'tcgdex') {
            // Find set by tcgdex_id
            $set = TcgdxSet::where('tcgdex_id', $setId)
                ->where('game_id', $currentGame->id)
                ->firstOrFail();
            
            $cards = TcgdxCard::where('set_tcgdx_id', $set->id)
                ->orderBy('local_id')
                ->paginate(50);
            
            return view('pokemon.catalog.set-cards-tcgdex', [
                'set' => $set,
                'cards' => $cards,
                'currentGame' => $currentGame,
                'backend' => 'tcgdex',
            ]);
        }
        
        // Use TCGCSV data (default)
        $set = TcgcsvGroup::where('group_id', $setId)
            ->where('game_id', $currentGame->id)
            ->firstOrFail();
        
        $cards = TcgcsvProduct::where('group_id', $setId)
            ->where('game_id', $currentGame->id)
            ->orderBy('product_id')
            ->paginate(50);
        
        return view('pokemon.catalog.set-cards', [
            'set' => $set,
            'cards' => $cards,
            'currentGame' => $currentGame,
            'backend' => 'tcgcsv',
        ]);
    }
    
    /**
     * Display single card details
     */
    public function card(Request $request, string $cardId): View
    {
        $currentGame = $request->attributes->get('currentGame');
        
        // If no current game (user not logged in), default to Pokemon
        if (!$currentGame) {
            $currentGame = \App\Models\Game::where('code', 'pokemon')->first();
            if (!$currentGame) {
                abort(404, 'Pokemon game not found');
            }
        }
        
        // Pokemon routes ALWAYS use TCGDEX when configured
        if (config('catalog.backend') === 'tcgdex') {
            $card = TcgdxCard::where('tcgdex_id', $cardId)
                ->whereHas('set', function($q) use ($currentGame) {
                    $q->where('game_id', $currentGame->id);
                })
                ->with('set')
                ->firstOrFail();
            
            // Get price history if cardmarket idProduct is available
            $priceHistory = $this->getCardmarketPriceHistory($card);
            
            // Get Cardmarket link from RapidAPI using cardmarket_id from TCGdex JSON
            $cardmarketUrl = null;
            $cardmarketId = $card->raw['pricing']['cardmarket']['idProduct'] ?? null;
            if ($cardmarketId) {
                $rapidCard = \App\Models\RapidapiCard::where('cardmarket_id', $cardmarketId)->first();
                if ($rapidCard && isset($rapidCard->links['cardmarket'])) {
                    $cardmarketUrl = $rapidCard->links['cardmarket'];
                }
            }
            
            return view('pokemon.catalog.card-tcgdex', [
                'card' => $card,
                'currentGame' => $currentGame,
                'backend' => 'tcgdex',
                'priceHistory' => $priceHistory,
                'cardmarketUrl' => $cardmarketUrl,
            ]);
        }
        
        // Use TCGCSV data (default)
        $card = TcgcsvProduct::where('product_id', $cardId)
            ->where('game_id', $currentGame->id)
            ->with('group')
            ->firstOrFail();
        
        return view('pokemon.catalog.card', [
            'card' => $card,
            'currentGame' => $currentGame,
            'backend' => 'tcgcsv',
        ]);
    }
    
    /**
     * Get price history from Cardmarket for a TcgdxCard
     */
    private function getCardmarketPriceHistory(TcgdxCard $card): array
    {
        // Extract cardmarket idProduct from raw data
        $pricing = $card->raw['pricing'] ?? null;
        $cardmarket = $pricing['cardmarket'] ?? null;
        $idProduct = $cardmarket['idProduct'] ?? null;
        
        if (!$idProduct) {
            return ['trend' => [], 'trend_holo' => []];
        }
        
        // Get last 30 days of quotes
        $quotes = \App\Models\CardmarketPriceQuote::where('cardmarket_product_id', $idProduct)
            ->where('as_of_date', '>=', now()->subDays(30))
            ->orderBy('as_of_date', 'asc')
            ->get();
        
        if ($quotes->isEmpty()) {
            return ['trend' => [], 'trend_holo' => []];
        }
        
        // Build trend data
        $trendData = [];
        $trendHoloData = [];
        
        foreach ($quotes as $quote) {
            if ($quote->trend !== null) {
                $trendData[] = [
                    'x' => $quote->as_of_date->format('Y-m-d'),
                    'y' => (float) $quote->trend
                ];
            }
            
            if ($quote->trend_holo !== null) {
                $trendHoloData[] = [
                    'x' => $quote->as_of_date->format('Y-m-d'),
                    'y' => (float) $quote->trend_holo
                ];
            }
        }
        
        return [
            'trend' => $trendData,
            'trend_holo' => $trendHoloData,
        ];
    }
}
