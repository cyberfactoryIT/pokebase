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
        
        // Use the catalog backend configured for this game
        if ($currentGame->catalog_backend === 'tcgdex') {
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
        
        // Use the catalog backend configured for this game
        if ($currentGame->catalog_backend === 'tcgdex') {
            // Find set by tcgdex_id
            $set = TcgdxSet::where('tcgdex_id', $setId)
                ->where('game_id', $currentGame->id)
                ->firstOrFail();
            
            $cards = TcgdxCard::where('set_tcgdx_id', $set->id)
                ->orderBy('local_id')
                ->paginate(50);
            
            // Get user interactions if authenticated
            $userInteractions = null;
            if (\Auth::check()) {
                $user = \Auth::user();
                $cardIds = $cards->pluck('id')->toArray();
                
                // Get all liked cards in one query
                $likedIds = \DB::table('user_likes')
                    ->where('user_id', $user->id)
                    ->whereIn('tcgdex_card_id', $cardIds)
                    ->pluck('tcgdex_card_id')
                    ->toArray();
                
                // Get all wishlist cards in one query
                $wishlistIds = \DB::table('user_wishlist_items')
                    ->where('user_id', $user->id)
                    ->whereIn('tcgdex_card_id', $cardIds)
                    ->pluck('tcgdex_card_id')
                    ->toArray();
                
                // Get all watched cards in one query
                $watchIds = \DB::table('user_watch_items')
                    ->where('user_id', $user->id)
                    ->whereIn('tcgdex_card_id', $cardIds)
                    ->pluck('tcgdex_card_id')
                    ->toArray();
                
                $userInteractions = [
                    'liked' => $likedIds,
                    'wishlist' => $wishlistIds,
                    'watched' => $watchIds,
                ];
            }
            
            return view('pokemon.catalog.set-cards-tcgdex', [
                'set' => $set,
                'cards' => $cards,
                'currentGame' => $currentGame,
                'backend' => 'tcgdex',
                'userInteractions' => $userInteractions,
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
        
        // Use the catalog backend configured for this game
        if ($currentGame->catalog_backend === 'tcgdex') {
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
            
            // Check user interactions state (if authenticated)
            $isLiked = false;
            $isWishlisted = false;
            $isWatched = false;
            
            if (\Auth::check()) {
                $userId = \Auth::id();
                $isLiked = \DB::table('user_likes')
                    ->where('user_id', $userId)
                    ->where('tcgdex_card_id', $card->id)
                    ->exists();
                    
                $isWishlisted = \DB::table('user_wishlist_items')
                    ->where('user_id', $userId)
                    ->where('tcgdex_card_id', $card->id)
                    ->exists();
                    
                $isWatched = \DB::table('user_watch_items')
                    ->where('user_id', $userId)
                    ->where('tcgdex_card_id', $card->id)
                    ->exists();
            }
            
            return view('pokemon.catalog.card-tcgdex', [
                'card' => $card,
                'currentGame' => $currentGame,
                'backend' => 'tcgdex',
                'priceHistory' => $priceHistory,
                'cardmarketUrl' => $cardmarketUrl,
                'isLiked' => $isLiked,
                'isWishlisted' => $isWishlisted,
                'isWatched' => $isWatched,
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

    /**
     * AJAX search endpoint for sets
     */
    public function setsSearch(Request $request)
    {
        $validated = $request->validate([
            'query' => 'nullable|string|max:100',
            'page' => 'integer|min:1',
        ]);

        $currentGame = $request->attributes->get('currentGame');
        
        // If no current game (user not logged in), default to Pokemon
        if (!$currentGame) {
            $currentGame = \App\Models\Game::where('code', 'pokemon')->first();
            if (!$currentGame) {
                abort(404, 'Pokemon game not found');
            }
        }

        $query = TcgdxSet::where('game_id', $currentGame->id);

        // Search filter
        if (!empty($validated['query'])) {
            $searchTerm = $validated['query'];
            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(JSON_EXTRACT(name, "$.en")) LIKE LOWER(?)', ["%{$searchTerm}%"])
                  ->orWhere('tcgdex_id', 'like', "%{$searchTerm}%");
            });
        }

        // Order by release date descending
        $query->orderByDesc('release_date');

        // Paginate
        $sets = $query->paginate(24);

        // Map results
        $data = $sets->map(function($set) {
            return [
                'tcgdex_id' => $set->tcgdex_id,
                'name' => $set->getLocalizedName(),
                'series' => $set->series,
                'release_date' => $set->release_date ? $set->release_date->format('Y-m-d') : null,
                'logo_url' => $set->logo_url,
                'symbol_url' => $set->symbol_url,
                'card_count_total' => $set->card_count_total,
                'card_count_official' => $set->card_count_official,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $sets->currentPage(),
                'last_page' => $sets->lastPage(),
                'per_page' => $sets->perPage(),
                'total' => $sets->total(),
            ],
        ]);
    }

    /**
     * AJAX search endpoint for cards in a set
     */
    public function setCardsSearch(Request $request, string $setId)
    {
        $validated = $request->validate([
            'query' => 'nullable|string|max:100',
            'page' => 'integer|min:1',
        ]);

        $currentGame = $request->attributes->get('currentGame');
        
        if (!$currentGame) {
            $currentGame = \App\Models\Game::where('code', 'pokemon')->first();
            if (!$currentGame) {
                abort(404, 'Pokemon game not found');
            }
        }

        // Find set
        $set = TcgdxSet::where('tcgdex_id', $setId)
            ->where('game_id', $currentGame->id)
            ->firstOrFail();

        $query = TcgdxCard::where('set_tcgdx_id', $set->id);

        // Search filter
        if (!empty($validated['query'])) {
            $searchTerm = $validated['query'];
            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(JSON_EXTRACT(name, "$.en")) LIKE LOWER(?)', ["%{$searchTerm}%"])
                  ->orWhere('tcgdex_id', 'like', "%{$searchTerm}%")
                  ->orWhere('local_id', 'like', "%{$searchTerm}%");
            });
        }

        // Order by local_id
        $query->orderBy('local_id');

        // Paginate
        $cards = $query->paginate(50);

        // Get user interactions if authenticated
        $userInteractions = [
            'liked' => [],
            'wishlist' => [],
            'watched' => [],
        ];

        if (\Auth::check()) {
            $user = \Auth::user();
            $cardIds = $cards->pluck('id')->toArray();
            
            $userInteractions['liked'] = \DB::table('user_likes')
                ->where('user_id', $user->id)
                ->whereIn('tcgdex_card_id', $cardIds)
                ->pluck('tcgdex_card_id')
                ->toArray();
            
            $userInteractions['wishlist'] = \DB::table('user_wishlist_items')
                ->where('user_id', $user->id)
                ->whereIn('tcgdex_card_id', $cardIds)
                ->pluck('tcgdex_card_id')
                ->toArray();
            
            $userInteractions['watched'] = \DB::table('user_watch_items')
                ->where('user_id', $user->id)
                ->whereIn('tcgdex_card_id', $cardIds)
                ->pluck('tcgdex_card_id')
                ->toArray();
        }

        // Map results
        $data = $cards->map(function($card) use ($userInteractions) {
            return [
                'id' => $card->id,
                'tcgdex_id' => $card->tcgdex_id,
                'name' => $card->getLocalizedName(),
                'local_id' => $card->local_id,
                'number' => $card->number,
                'rarity' => $card->rarity,
                'price_eur' => $card->price_eur,
                'image_small_url' => $card->image_small_url,
                'is_liked' => in_array($card->id, $userInteractions['liked']),
                'is_wishlisted' => in_array($card->id, $userInteractions['wishlist']),
                'is_watched' => in_array($card->id, $userInteractions['watched']),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $cards->currentPage(),
                'last_page' => $cards->lastPage(),
                'per_page' => $cards->perPage(),
                'total' => $cards->total(),
            ],
        ]);
    }
}
