<?php

namespace App\Http\Controllers;

use App\Models\TcgcsvProduct;
use App\Models\TcgcsvGroup;
use App\Models\Tcgdx\TcgdxCard;
use App\Models\Tcgdx\TcgdxSet;
use App\Models\Deck;
use App\Models\UserCollection;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard
     */
    public function index(Request $request): View|RedirectResponse
    {
        // Check if user is superadmin and redirect to superadmin dashboard
        $user = auth()->user();
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($user->organization_id);
        
        if ($user->hasRole('superadmin')) {
            return redirect()->route('superadmin.dashboard');
        }
        
        $currentGame = $request->attributes->get('currentGame');
        $catalogBackend = catalog_backend();
        
        // If no game selected, show empty state
        if (!$currentGame) {
            return view('dashboard', [
                'cardsCount' => 0,
                'expansionsCount' => 0,
                'userDecksCount' => 0,
                'userCollectionCount' => 0,
                'uniqueCardsCount' => 0,
                'collectionValue' => 0,
                'currentGame' => null,
                'articles' => collect(),
                'articleCategories' => collect(),
                'userLocale' => app()->getLocale(),
                'recentAdditions' => collect(),
                'topCards' => collect(),
                'userExpansions' => collect(),
            ]);
        }
        
        // Get counts filtered by current game and catalog backend
        if ($catalogBackend === 'tcgdex') {
            $cardsCount = \App\Models\Tcgdx\TcgdxCard::count();
            $expansionsCount = \App\Models\Tcgdx\TcgdxSet::count();
        } else {
            $cardsCount = TcgcsvProduct::where('game_id', $currentGame->id)->count();
            $expansionsCount = TcgcsvGroup::where('game_id', $currentGame->id)->count();
        }
        
        $userDecksCount = Deck::where('user_id', Auth::id())
            ->where('game_id', $currentGame->id)
            ->count();
        
        // Get user collection stats for current game
        if ($catalogBackend === 'tcgdex') {
            $userCollectionCount = UserCollection::where('user_id', Auth::id())
                ->whereNotNull('tcgdex_card_id')
                ->sum('quantity');
                
            $uniqueCardsCount = UserCollection::where('user_id', Auth::id())
                ->whereNotNull('tcgdex_card_id')
                ->count();
        } else {
            $userCollectionCount = UserCollection::where('user_id', Auth::id())
                ->whereHas('card', function($q) use ($currentGame) {
                    $q->where('game_id', $currentGame->id);
                })
                ->sum('quantity');
                
            $uniqueCardsCount = UserCollection::where('user_id', Auth::id())
                ->whereHas('card', function($q) use ($currentGame) {
                    $q->where('game_id', $currentGame->id);
                })
                ->count();
        }
        
        // Get current locale (from session or user preference)
        $userLocale = app()->getLocale();
        
        // Get articles for current game
        $articlesQuery = Article::published()
            ->where('game_id', $currentGame->id);
        
        // Filter by category if specified
        if ($request->has('article_category') && $request->article_category) {
            $articlesQuery->where('category', $request->article_category);
        }
        
        $articles = $articlesQuery
            ->orderByRaw('sort_order is null, sort_order asc')
            ->orderByDesc('published_at')
            ->limit(9)
            ->get();
        
        // Get available categories for this game
        $articleCategories = Article::published()
            ->where('game_id', $currentGame->id)
            ->distinct()
            ->pluck('category')
            ->sort();
        
        // Get recent additions (last 6 cards added to collection)
        if ($catalogBackend === 'tcgdex') {
            $recentAdditions = UserCollection::where('user_id', Auth::id())
                ->whereNotNull('tcgdex_card_id')
                ->with('tcgdexCard')
                ->orderByDesc('created_at')
                ->limit(6)
                ->get();
        } else {
            $recentAdditions = UserCollection::where('user_id', Auth::id())
                ->whereHas('card', function($q) use ($currentGame) {
                    $q->where('game_id', $currentGame->id);
                })
                ->with(['card.group', 'card.prices'])
                ->orderByDesc('created_at')
                ->limit(6)
                ->get();
        }
        
        // Get top 5 most valuable cards in collection
        if ($catalogBackend === 'tcgdex') {
            $topCards = UserCollection::where('user_id', Auth::id())
                ->whereNotNull('tcgdex_card_id')
                ->whereNotNull('cached_price')
                ->with('tcgdexCard')
                ->orderByDesc('cached_price')
                ->limit(5)
                ->get();
        } else {
            $topCards = UserCollection::where('user_id', Auth::id())
                ->whereHas('card', function($q) use ($currentGame) {
                    $q->where('game_id', $currentGame->id);
                })
                ->with(['card.group', 'card.prices'])
                ->limit(50) // Reduced from 100 for better performance
                ->get()
                ->groupBy('product_id')
                ->map(function($items) {
                    // Merge duplicate entries of the same card
                    $first = $items->first();
                    $first->quantity = $items->sum('quantity');
                    return $first;
                })
                ->sortByDesc(function($item) {
                    // Use cached_price if available
                    if ($item->cached_price && $item->cached_price > 0) {
                        return $item->cached_price;
                    }
                    
                    // Use EUR price (most reliable for European market)
                    // Priority 1: Cardmarket price quotes (latest trend)
                    $cardmarketProduct = $item->card->cardmarketProduct;
                    if ($cardmarketProduct) {
                        $latestQuote = $cardmarketProduct->latestPriceQuote;
                        if ($latestQuote && $latestQuote->trend > 0) {
                            return $latestQuote->trend;
                        }
                        if ($latestQuote && $latestQuote->avg > 0) {
                            return $latestQuote->avg;
                        }
                    }
                    
                    // Priority 2: Cardmarket EUR from tcgcsv_products
                    if ($item->card->cardmarket_price_eur && $item->card->cardmarket_price_eur > 0) {
                        return $item->card->cardmarket_price_eur;
                    }
                    
                    // Priority 3: RapidAPI Cardmarket data
                    $rapidapiCard = $item->card->rapidapiCard;
                    if ($rapidapiCard && isset($rapidapiCard->raw_data['prices']['cardmarket']['lowest_near_mint'])) {
                        $eurPrice = (float) $rapidapiCard->raw_data['prices']['cardmarket']['lowest_near_mint'];
                        if ($eurPrice > 0) return $eurPrice;
                    }
                    
                    // Priority 4: Convert USD to EUR as fallback
                    $latestPrice = $item->card->prices->first();
                    if ($latestPrice?->market_price) {
                        return convertUsdToEur($latestPrice->market_price);
                    }
                    return 0;
                })
                ->take(5);
        }
        
        // Calculate total collection value (in EUR base)
        // TEMPORARILY DISABLED: This query causes memory exhaustion
        $collectionValue = 0;
        
        /*
        $collectionValue = UserCollection::where('user_id', Auth::id())
            ->whereHas('card', function($q) use ($currentGame) {
                $q->where('game_id', $currentGame->id);
            })
            ->with(['card.prices', 'card.rapidapiCard', 'card.cardmarketProduct.latestPriceQuote'])
            ->get()
            ->sum(function($item) {
                $priceEur = 0;
                
                // Priority 1: Cardmarket price quotes (latest trend)
                $cardmarketProduct = $item->card->cardmarketProduct;
                if ($cardmarketProduct) {
                    $latestQuote = $cardmarketProduct->latestPriceQuote;
                    if ($latestQuote && $latestQuote->trend > 0) {
                        $priceEur = $latestQuote->trend;
                    } elseif ($latestQuote && $latestQuote->avg > 0) {
                        $priceEur = $latestQuote->avg;
                    }
                }
                
                // Priority 2: Cardmarket EUR from tcgcsv_products
                if ($priceEur === 0 && $item->card->cardmarket_price_eur && $item->card->cardmarket_price_eur > 0) {
                    $priceEur = $item->card->cardmarket_price_eur;
                }
                
                // Priority 3: RapidAPI Cardmarket data
                if ($priceEur === 0) {
                    $rapidapiCard = $item->card->rapidapiCard;
                    if ($rapidapiCard && isset($rapidapiCard->raw_data['prices']['cardmarket']['lowest_near_mint'])) {
                        $priceEur = (float) $rapidapiCard->raw_data['prices']['cardmarket']['lowest_near_mint'];
                    }
                }
                
                // Priority 3: Convert USD to EUR as fallback
                if ($priceEur === 0) {
                    $latestPrice = $item->card->prices->first();
                    if ($latestPrice?->market_price) {
                        $priceEur = convertUsdToEur($latestPrice->market_price);
                    }
                }
                
                return $priceEur * $item->quantity;
            });
        */
        
        // Get expansions for missing cards dropdown
        // Get unique group_ids from user's collection for this game
        if ($catalogBackend === 'tcgdex') {
            $userSetIds = UserCollection::where('user_id', Auth::id())
                ->join('tcgdx_cards', 'user_collection.tcgdex_card_id', '=', 'tcgdx_cards.id')
                ->distinct()
                ->pluck('tcgdx_cards.set_tcgdx_id');
            
            $userExpansions = \App\Models\Tcgdx\TcgdxSet::whereIn('id', $userSetIds)
                ->orderBy('released_at', 'desc')
                ->limit(10)
                ->get();
            
            // Featured expansions for carousel
            $featuredExpansions = \App\Models\Tcgdx\TcgdxSet::orderBy('released_at', 'desc')
                ->limit(6)
                ->get();
        } else {
            $userGroupIds = UserCollection::where('user_id', Auth::id())
                ->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.product_id')
                ->where('tcgcsv_products.game_id', $currentGame->id)
                ->distinct()
                ->pluck('tcgcsv_products.group_id');
            
            $userExpansions = TcgcsvGroup::where('game_id', $currentGame->id)
                ->whereIn('group_id', $userGroupIds)
                ->orderBy('published_on', 'desc')
                ->limit(10)
                ->get();
            
            // Featured expansions for carousel
            $featuredExpansions = TcgcsvGroup::where('game_id', $currentGame->id)
                ->where('show_in_carousel', true)
                ->orderBy('published_on', 'desc')
                ->get();
        }
        
        return view('dashboard', [
            'catalogBackend' => $catalogBackend,
            'cardsCount' => $cardsCount,
            'expansionsCount' => $expansionsCount,
            'userDecksCount' => $userDecksCount,
            'userCollectionCount' => $userCollectionCount,
            'uniqueCardsCount' => $uniqueCardsCount,
            'collectionValue' => $collectionValue,
            'articles' => $articles,
            'articleCategories' => $articleCategories,
            'userLocale' => $userLocale,
            'recentAdditions' => $recentAdditions,
            'topCards' => $topCards,
            'userExpansions' => $userExpansions,
            'featuredExpansions' => $featuredExpansions,
            'currentGame' => $currentGame,
        ]);
    }
}
