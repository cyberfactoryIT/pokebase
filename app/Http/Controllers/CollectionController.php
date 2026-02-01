<?php

namespace App\Http\Controllers;

use App\Models\UserCollection;
use App\Models\UserCardPhoto;
use App\Models\TcgcsvProduct;
use App\Services\CollectionInsightsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CollectionController extends Controller
{
    /**
     * Display user's collection
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $currentGame = $request->attributes->get('currentGame');
        $catalogBackend = catalog_backend();
        $rarityFilter = $request->input('rarity');
        
        $query = UserCollection::where('user_id', $userId);
        
        // Load relations based on catalog backend
        if ($catalogBackend === 'tcgdex') {
            $query->with(['tcgdexCard', 'photos'])
                  ->whereNotNull('tcgdex_card_id');
            
            // Apply rarity filter for TCGDEX
            if ($rarityFilter) {
                $query->whereHas('tcgdexCard', function($q) use ($rarityFilter) {
                    $q->where('rarity', $rarityFilter);
                });
            }
        } else {
            $query->with(['card.group', 'card.rapidapiCard', 'card.prices', 'card.cardmarketProduct.latestPriceQuote', 'photos'])
                  ->whereNotNull('product_id');
                  
            // Filter by current game (only for TCGCSV)
            if ($currentGame) {
                $query->whereHas('card', function($q) use ($currentGame) {
                    $q->where('game_id', $currentGame->id);
                });
            }
            
            // Apply rarity filter for TCGCSV
            if ($rarityFilter) {
                $query->whereHas('card', function($q) use ($rarityFilter) {
                    $q->where('rarity', $rarityFilter);
                });
            }
        }
        
        $collection = $query->orderBy('created_at', 'desc')
            ->paginate(24);

        // Basic stats (filtered by game)
        $stats = [
            'total_cards' => $this->getUserCardCount($userId, $currentGame, $catalogBackend),
            'unique_cards' => $this->getUserUniqueCardCount($userId, $currentGame, $catalogBackend),
            'foil_cards' => $this->getUserFoilCardCount($userId, $currentGame, $catalogBackend),
        ];

        // Top 3 interesting stats for header
        $topStats = $this->getTopStats($userId, $currentGame, $catalogBackend);
        
        // Detailed statistics for stats tab
        $detailedStats = $this->getDetailedStats($userId, $currentGame, $catalogBackend);
        
        // Generate insights for statistics tab
        $insightsService = new CollectionInsightsService();
        $rarityInsight = $insightsService->generateRarityInsight($topStats['rarity_distribution']);
        $conditionInsight = $insightsService->generateConditionInsight($detailedStats['condition_distribution']);
        $focusSet = $insightsService->identifyFocusSet($detailedStats['top_sets']);
        $setsInsight = $insightsService->generateSetsInsight($detailedStats['top_sets'], $focusSet ?? []);
        
        // Calculate collection value (with rarity filter applied)
        $valuation = $this->calculateCollectionValue($userId, $currentGame, $catalogBackend, $rarityFilter);

        return view('collection.index', compact('collection', 'stats', 'topStats', 'detailedStats', 'valuation', 'rarityInsight', 'conditionInsight', 'setsInsight', 'focusSet'));
    }
    
    private function getUserCardCount($userId, $currentGame, $catalogBackend)
    {
        $query = UserCollection::where('user_id', $userId);
        if ($currentGame) {
            $query->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
        }
        // Filter by catalog backend
        if ($catalogBackend === 'tcgdex') {
            $query->whereNotNull('tcgdex_card_id');
        } else {
            $query->whereNotNull('product_id');
        }
        return $query->sum('quantity');
    }
    
    private function getUserUniqueCardCount($userId, $currentGame, $catalogBackend)
    {
        $query = UserCollection::where('user_id', $userId);
        if ($currentGame) {
            $query->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
        }
        // Filter by catalog backend
        if ($catalogBackend === 'tcgdex') {
            $query->whereNotNull('tcgdex_card_id');
        } else {
            $query->whereNotNull('product_id');
        }
        return $query->count();
    }
    
    private function getUserFoilCardCount($userId, $currentGame, $catalogBackend)
    {
        $query = UserCollection::where('user_id', $userId)->where('is_foil', true);
        if ($currentGame) {
            $query->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
        }
        // Filter by catalog backend
        if ($catalogBackend === 'tcgdex') {
            $query->whereNotNull('tcgdex_card_id');
        } else {
            $query->whereNotNull('product_id');
        }
        return $query->sum('quantity');
    }
    
    /**
     * Get top 3 interesting stats for header
     */
    private function getTopStats($userId, $currentGame, $catalogBackend): array
    {
        // 1. Rarity distribution (most interesting)
        if ($catalogBackend === 'tcgdex') {
            // TCGDEX: rarity is stored in JSON
            $rarityQuery = UserCollection::where('user_id', $userId)
                ->join('tcgdx_cards', 'user_collection.tcgdex_card_id', '=', 'tcgdx_cards.id')
                ->selectRaw('tcgdx_cards.rarity, COUNT(*) as count, SUM(user_collection.quantity) as total_quantity')
                ->whereNotNull('user_collection.tcgdex_card_id')
                ->groupBy('tcgdx_cards.rarity')
                ->orderBy('count', 'desc');
            $rarityDistribution = $rarityQuery->get();
        } else {
            // TCGCSV
            $rarityQuery = UserCollection::where('user_id', $userId)
                ->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.product_id')
                ->selectRaw('tcgcsv_products.rarity, COUNT(*) as count, SUM(user_collection.quantity) as total_quantity')
                ->whereNotNull('user_collection.product_id')
                ->groupBy('tcgcsv_products.rarity')
                ->orderBy('count', 'desc');
                
            if ($currentGame) {
                $rarityQuery->where('tcgcsv_products.game_id', $currentGame->id);
            }
            $rarityDistribution = $rarityQuery->get();
        }
        
        // 2. Foil percentage
        $totalCards = $this->getUserCardCount($userId, $currentGame, $catalogBackend);
        $foilCards = $this->getUserFoilCardCount($userId, $currentGame, $catalogBackend);
        $foilPercentage = $totalCards > 0 ? round(($foilCards / $totalCards) * 100, 1) : 0;
        
        // 3. Set completion (top set)
        if ($catalogBackend === 'tcgdex') {
            // TCGDEX
            $topSetQuery = UserCollection::where('user_id', $userId)
                ->join('tcgdx_cards', 'user_collection.tcgdex_card_id', '=', 'tcgdx_cards.id')
                ->join('tcgdx_sets', 'tcgdx_cards.set_tcgdx_id', '=', 'tcgdx_sets.id')
                ->selectRaw('tcgdx_sets.id, tcgdx_sets.name, COUNT(DISTINCT user_collection.tcgdex_card_id) as owned_count')
                ->whereNotNull('user_collection.tcgdex_card_id')
                ->groupBy('tcgdx_sets.id', 'tcgdx_sets.name')
                ->orderBy('owned_count', 'desc')
                ->first();
            
            $setCompletion = null;
            if ($topSetQuery) {
                $setName = $topSetQuery->name;
                if (is_string($setName) && str_starts_with($setName, '{')) {
                    $setName = json_decode($setName, true);
                }
                $setNameEn = is_array($setName) ? ($setName['en'] ?? $setName['fr'] ?? 'Unknown') : $setName;
                
                $totalInSet = \App\Models\Tcgdx\TcgdxCard::where('set_tcgdx_id', $topSetQuery->id)->count();
                $completionPercentage = $totalInSet > 0 ? round(($topSetQuery->owned_count / $totalInSet) * 100, 1) : 0;
                $setCompletion = [
                    'name' => $setNameEn,
                    'owned' => $topSetQuery->owned_count,
                    'total' => $totalInSet,
                    'percentage' => $completionPercentage
                ];
            }
        } else {
            // TCGCSV
            $topSetQuery = UserCollection::where('user_id', $userId)
                ->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.product_id')
                ->join('tcgcsv_groups', 'tcgcsv_products.group_id', '=', 'tcgcsv_groups.group_id')
                ->selectRaw('tcgcsv_groups.group_id, tcgcsv_groups.name, COUNT(DISTINCT user_collection.product_id) as owned_count')
                ->whereNotNull('user_collection.product_id')
                ->groupBy('tcgcsv_groups.group_id', 'tcgcsv_groups.name')
                ->orderBy('owned_count', 'desc');
                
            if ($currentGame) {
                $topSetQuery->where('tcgcsv_groups.game_id', $currentGame->id);
            }
            $topSet = $topSetQuery->first();
            
            $setCompletion = null;
            if ($topSet) {
                $totalInSetQuery = TcgcsvProduct::where('group_id', $topSet->group_id);
                if ($currentGame) {
                    $totalInSetQuery->where('game_id', $currentGame->id);
                }
                $totalInSet = $totalInSetQuery->count();
                $completionPercentage = $totalInSet > 0 ? round(($topSet->owned_count / $totalInSet) * 100, 1) : 0;
                $setCompletion = [
                    'name' => $topSet->name,
                    'owned' => $topSet->owned_count,
                    'total' => $totalInSet,
                    'percentage' => $completionPercentage
                ];
            }
        }
        
        return [
            'rarity_distribution' => $rarityDistribution,
            'foil_percentage' => $foilPercentage,
            'foil_count' => $foilCards,
            'total_count' => $totalCards,
            'set_completion' => $setCompletion
        ];
    }
    
    /**
     * Get detailed statistics for stats tab
     */
    private function getDetailedStats($userId, $currentGame, $catalogBackend): array
    {
        // Condition distribution
        $conditionQuery = UserCollection::where('user_id', $userId)
            ->selectRaw('`condition`, COUNT(*) as count, SUM(quantity) as total_quantity')
            ->groupBy('condition');
        if ($currentGame) {
            $conditionQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
        }
        // Filter by catalog backend
        if ($catalogBackend === 'tcgdex') {
            $conditionQuery->whereNotNull('tcgdex_card_id');
        } else {
            $conditionQuery->whereNotNull('product_id');
        }
        $conditionDistribution = $conditionQuery
            ->get();
        
        // Cards with notes
        $cardsWithNotesQuery = UserCollection::where('user_id', $userId)
            ->whereNotNull('notes')
            ->where('notes', '!=', '');
        if ($currentGame) {
            $cardsWithNotesQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
        }
        // Filter by catalog backend
        if ($catalogBackend === 'tcgdex') {
            $cardsWithNotesQuery->whereNotNull('tcgdex_card_id');
        } else {
            $cardsWithNotesQuery->whereNotNull('product_id');
        }
        $cardsWithNotes = $cardsWithNotesQuery->count();
        
        // Duplicate cards (quantity > 1)
        $duplicateCardsQuery = UserCollection::where('user_id', $userId)
            ->where('quantity', '>', 1);
        if ($currentGame) {
            $duplicateCardsQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
        }
        // Filter by catalog backend
        if ($catalogBackend === 'tcgdex') {
            $duplicateCardsQuery->whereNotNull('tcgdex_card_id');
        } else {
            $duplicateCardsQuery->whereNotNull('product_id');
        }
        $duplicateCards = $duplicateCardsQuery->count();
        
        // Set statistics
        if ($catalogBackend === 'tcgdex') {
            // TCGDEX
            $setStatsQuery = UserCollection::where('user_id', $userId)
                ->join('tcgdx_cards', 'user_collection.tcgdex_card_id', '=', 'tcgdx_cards.id')
                ->selectRaw('COUNT(DISTINCT tcgdx_cards.set_tcgdx_id) as total_sets')
                ->whereNotNull('user_collection.tcgdex_card_id')
                ->first();
            $setStats = $setStatsQuery;
        } else {
            // TCGCSV
            $setStatsQuery = UserCollection::where('user_id', $userId)
                ->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.product_id')
                ->join('tcgcsv_groups', 'tcgcsv_products.group_id', '=', 'tcgcsv_groups.group_id')
                ->selectRaw('COUNT(DISTINCT tcgcsv_groups.group_id) as total_sets')
                ->whereNotNull('user_collection.product_id');
            if ($currentGame) {
                $setStatsQuery->where('tcgcsv_groups.game_id', $currentGame->id);
            }
            $setStats = $setStatsQuery->first();
        }
        
        // Top 5 sets by completion
        if ($catalogBackend === 'tcgdex') {
            // TCGDEX
            $topSetsQuery = UserCollection::where('user_id', $userId)
                ->join('tcgdx_cards', 'user_collection.tcgdex_card_id', '=', 'tcgdx_cards.id')
                ->join('tcgdx_sets', 'tcgdx_cards.set_tcgdx_id', '=', 'tcgdx_sets.id')
                ->selectRaw('tcgdx_sets.id as set_id, tcgdx_sets.name, COUNT(DISTINCT user_collection.tcgdex_card_id) as owned_count')
                ->whereNotNull('user_collection.tcgdex_card_id')
                ->groupBy('tcgdx_sets.id', 'tcgdx_sets.name')
                ->orderBy('owned_count', 'desc')
                ->limit(5)
                ->get()
                ->map(function($set) {
                    // Extract English name from JSON
                    $setName = $set->name;
                    if (is_string($setName) && str_starts_with($setName, '{')) {
                        $setName = json_decode($setName, true);
                    }
                    $setNameEn = is_array($setName) ? ($setName['en'] ?? $setName['fr'] ?? 'Unknown') : $setName;
                    
                    $totalInSet = \App\Models\Tcgdx\TcgdxCard::where('set_tcgdx_id', $set->set_id)->count();
                    $set->name = $setNameEn;
                    $set->total_in_set = $totalInSet;
                    $set->completion_percentage = $totalInSet > 0 ? round(($set->owned_count / $totalInSet) * 100, 1) : 0;
                    return $set;
                });
            $topSets = $topSetsQuery;
        } else {
            // TCGCSV
            $topSetsQuery = UserCollection::where('user_id', $userId)
                ->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.product_id')
                ->join('tcgcsv_groups', 'tcgcsv_products.group_id', '=', 'tcgcsv_groups.group_id')
                ->selectRaw('tcgcsv_groups.group_id, tcgcsv_groups.name, COUNT(DISTINCT user_collection.product_id) as owned_count')
                ->whereNotNull('user_collection.product_id')
                ->groupBy('tcgcsv_groups.group_id', 'tcgcsv_groups.name')
                ->orderBy('owned_count', 'desc')
                ->limit(5);
            if ($currentGame) {
                $topSetsQuery->where('tcgcsv_groups.game_id', $currentGame->id);
            }
            $topSets = $topSetsQuery->get()
                ->map(function($set) use ($currentGame) {
                    $totalQuery = TcgcsvProduct::where('group_id', $set->group_id);
                    if ($currentGame) {
                        $totalQuery->where('game_id', $currentGame->id);
                    }
                    $totalInSet = $totalQuery->count();
                    $set->total_in_set = $totalInSet;
                    $set->completion_percentage = $totalInSet > 0 ? round(($set->owned_count / $totalInSet) * 100, 1) : 0;
                    return $set;
                });
        }
        
        // Timeline - cards added by month (last 6 months)
        $timelineQuery = UserCollection::where('user_id', $userId)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'asc');
        if ($currentGame) {
            $timelineQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
        }
        // Filter by catalog backend
        if ($catalogBackend === 'tcgdex') {
            $timelineQuery->whereNotNull('tcgdex_card_id');
        } else {
            $timelineQuery->whereNotNull('product_id');
        }
        $timeline = $timelineQuery->get();
        
        return [
            'condition_distribution' => $conditionDistribution,
            'cards_with_notes' => $cardsWithNotes,
            'duplicate_cards' => $duplicateCards,
            'total_sets' => $setStats->total_sets ?? 0,
            'top_sets' => $topSets,
            'timeline' => $timeline
        ];
    }

    /**
     * Add a card to user's collection
     */
    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:tcgcsv_products,product_id',
            'quantity' => 'nullable|integer|min:1|max:99',
            'condition' => 'nullable|string|in:mint,near_mint,excellent,good,light_played,played,poor',
            'is_foil' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $quantityToAdd = $validated['quantity'] ?? 1;

        // Check card limit
        if (!\Gate::forUser($user)->allows('addCards', $quantityToAdd)) {
            $limit = $user->cardLimit();
            $currentUsage = $user->currentCardUsage();
            
            return back()->with('error', __('limits.cards.reached.title'))
                ->with('error_detail', __('limits.cards.reached.body_adding', [
                    'amount' => $quantityToAdd,
                    'limit' => $limit,
                    'used' => $currentUsage,
                ]));
        }

        // Check if card already exists with same condition/foil
        $existing = UserCollection::where('user_id', Auth::id())
            ->where('product_id', $validated['product_id'])
            ->where('condition', $validated['condition'] ?? null)
            ->where('is_foil', $validated['is_foil'] ?? false)
            ->first();

        if ($existing) {
            // Increment quantity
            $existing->increment('quantity', $quantityToAdd);
            $message = 'Card quantity updated in your collection!';
        } else {
            // Get card price from catalog
            $card = TcgcsvProduct::find($validated['product_id']);
            $price = null;
            $currency = 'USD';
            
            if ($card) {
                // Try to get price from latest price record
                $latestPrice = $card->prices()->orderBy('updated_at', 'desc')->first();
                if ($latestPrice && $latestPrice->market_price) {
                    $price = $latestPrice->market_price;
                    $currency = 'USD';
                }
            }
            
            // Create new entry
            UserCollection::create([
                'user_id' => Auth::id(),
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'] ?? 1,
                'condition' => $validated['condition'] ?? null,
                'is_foil' => $validated['is_foil'] ?? false,
                'notes' => $validated['notes'] ?? null,
                'cached_price' => $price,
                'cached_price_currency' => $currency,
                'cached_price_updated_at' => $price ? now() : null,
            ]);
            $message = 'Card added to your collection!';
        }

        return back()->with('success', $message);
    }

    /**
     * Add a TCGDEX card to user's collection
     */
    public function addTcgdex(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tcgdex_card_id' => 'required|integer|exists:tcgdx_cards,id',
            'quantity' => 'nullable|integer|min:1|max:99',
            'condition' => 'nullable|string|in:mint,near_mint,excellent,good,light_played,played,poor',
            'is_foil' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $quantityToAdd = $validated['quantity'] ?? 1;

        // Check card limit
        if (!\Gate::forUser($user)->allows('addCards', $quantityToAdd)) {
            $limit = $user->cardLimit();
            $currentUsage = $user->currentCardUsage();
            
            return back()->with('error', __('limits.cards.reached.title'))
                ->with('error_detail', __('limits.cards.reached.body_adding', [
                    'amount' => $quantityToAdd,
                    'limit' => $limit,
                    'used' => $currentUsage,
                ]));
        }

        // Check if card already exists with same condition/foil
        $existing = UserCollection::where('user_id', Auth::id())
            ->where('tcgdex_card_id', $validated['tcgdex_card_id'])
            ->where('condition', $validated['condition'] ?? null)
            ->where('is_foil', $validated['is_foil'] ?? false)
            ->first();

        if ($existing) {
            // Increment quantity
            $existing->increment('quantity', $quantityToAdd);
            $message = 'Card quantity updated in your collection!';
        } else {
            // Get card price from catalog
            $card = \App\Models\Tcgdx\TcgdxCard::find($validated['tcgdex_card_id']);
            $price = null;
            $currency = 'EUR';
            
            if ($card) {
                // Use EUR price if available, fallback to USD
                if ($card->price_eur && $card->price_eur > 0) {
                    $price = $card->price_eur;
                    $currency = 'EUR';
                } elseif ($card->price_usd && $card->price_usd > 0) {
                    $price = $card->price_usd;
                    $currency = 'USD';
                }
            }
            
            // Create new entry
            UserCollection::create([
                'user_id' => Auth::id(),
                'tcgdex_card_id' => $validated['tcgdex_card_id'],
                'quantity' => $validated['quantity'] ?? 1,
                'condition' => $validated['condition'] ?? null,
                'is_foil' => $validated['is_foil'] ?? false,
                'notes' => $validated['notes'] ?? null,
                'cached_price' => $price,
                'cached_price_currency' => $currency,
                'cached_price_updated_at' => $price ? now() : null,
            ]);
            $message = 'Card added to your collection!';
        }

        return back()->with('success', $message);
    }

    /**
     * Add a CMAPI card (Lorcana/One Piece) to user's collection
     */
    public function addCmapi(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cmapi_card_id' => 'required|string|max:100|exists:cmapi_cards,cmapi_id',
            'quantity' => 'nullable|integer|min:1|max:99',
            'condition' => 'nullable|string|in:mint,near_mint,excellent,good,light_played,played,poor',
            'is_foil' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $quantityToAdd = $validated['quantity'] ?? 1;

        // Check card limit
        if (!\Gate::forUser($user)->allows('addCards', $quantityToAdd)) {
            $limit = $user->cardLimit();
            $currentUsage = $user->currentCardUsage();
            
            return back()->with('error', __('limits.cards.reached.title'))
                ->with('error_detail', __('limits.cards.reached.body_adding', [
                    'amount' => $quantityToAdd,
                    'limit' => $limit,
                    'used' => $currentUsage,
                ]));
        }

        // Check if card already exists with same condition/foil
        $existing = UserCollection::where('user_id', Auth::id())
            ->where('cmapi_card_id', $validated['cmapi_card_id'])
            ->where('condition', $validated['condition'] ?? null)
            ->where('is_foil', $validated['is_foil'] ?? false)
            ->first();

        if ($existing) {
            // Increment quantity
            $existing->increment('quantity', $quantityToAdd);
            $message = 'Card quantity updated in your collection!';
        } else {
            // Get card price from catalog
            $card = \App\Models\Cmapi\CmapiCard::where('cmapi_id', $validated['cmapi_card_id'])->first();
            $price = null;
            $currency = 'EUR';
            
            if ($card && $card->price_eur && $card->price_eur > 0) {
                $price = $card->price_eur;
                $currency = 'EUR';
            }
            
            // Create new entry
            UserCollection::create([
                'user_id' => Auth::id(),
                'cmapi_card_id' => $validated['cmapi_card_id'],
                'quantity' => $validated['quantity'] ?? 1,
                'condition' => $validated['condition'] ?? null,
                'is_foil' => $validated['is_foil'] ?? false,
                'notes' => $validated['notes'] ?? null,
                'cached_price' => $price,
                'cached_price_currency' => $currency,
                'cached_price_updated_at' => $price ? now() : null,
            ]);
            $message = 'Card added to your collection!';
        }

        return back()->with('success', $message);
    }

    /**
     * Remove a card from collection
     */
    public function remove($id): RedirectResponse
    {
        $collectionItem = UserCollection::findOrFail($id);
        
        // Authorization check
        if ($collectionItem->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $collectionItem->delete();

        return back()->with('success', 'Card removed from collection!');
    }

    /**
     * Update card quantity or details
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $collectionItem = UserCollection::findOrFail($id);
        
        // Authorization check
        if ($collectionItem->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
            'condition' => 'nullable|string|in:mint,near_mint,excellent,good,light_played,played,poor',
            'is_foil' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $collectionItem->update($validated);

        return back()->with('success', 'Collection item updated!');
    }

    /**
     * Check if a card is in user's collection
     */
    public function checkCard(int $productId)
    {
        $items = UserCollection::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->get();

        return response()->json([
            'in_collection' => $items->isNotEmpty(),
            'total_quantity' => $items->sum('quantity'),
            'items' => $items,
        ]);
    }
    
    /**
     * Calculate total collection value in USD and EUR
     * Uses cached prices for performance, falls back to real-time queries if cache is null
     */
    private function calculateCollectionValue($userId, $currentGame, $catalogBackend, $rarityFilter = null): array
    {
        $user = \App\Models\User::find($userId);
        $preferredCurrency = $user->preferred_currency ?? 'USD';
        
        // Try cached prices first (fast query)
        $cachedQuery = UserCollection::where('user_id', $userId)
            ->whereNotNull('cached_price');
            
        if ($catalogBackend === 'tcgdex') {
            $cachedQuery->whereNotNull('tcgdex_card_id');
            // No currentGame filter for TCGDEX (it's always Pokemon)
            
            // Apply rarity filter for TCGDEX
            if ($rarityFilter) {
                $cachedQuery->whereHas('tcgdexCard', function($q) use ($rarityFilter) {
                    $q->where('rarity', $rarityFilter);
                });
            }
        } else {
            $cachedQuery->whereNotNull('product_id');
            // Filter by current game (only for TCGCSV)
            if ($currentGame) {
                $cachedQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
            }
            
            // Apply rarity filter for TCGCSV
            if ($rarityFilter) {
                $cachedQuery->whereHas('card', function($q) use ($rarityFilter) {
                    $q->where('rarity', $rarityFilter);
                });
            }
        }
        
        $cachedItems = $cachedQuery->get();
        
        // Calculate from cached prices
        $totalValueUsd = 0;
        $totalValueEur = 0;
        $cardsWithCachedPrices = $cachedItems->count();
        
        foreach ($cachedItems as $item) {
            // All cached prices are now in EUR (from price_eur or cardmarket_price_eur)
            $totalValueEur += $item->cached_price * $item->quantity;
            // Convert to USD (approximate)
            $totalValueUsd += ($item->cached_price * 1.10) * $item->quantity;
        }
        
        // Fallback: Get items without cached prices and calculate real-time
        $uncachedQuery = UserCollection::where('user_id', $userId)
            ->whereNull('cached_price');
            
        if ($catalogBackend === 'tcgdex') {
            $uncachedQuery->whereNotNull('tcgdex_card_id')
                         ->with('tcgdexCard');
            // No currentGame filter for TCGDEX
            
            // Apply rarity filter for TCGDEX
            if ($rarityFilter) {
                $uncachedQuery->whereHas('tcgdexCard', function($q) use ($rarityFilter) {
                    $q->where('rarity', $rarityFilter);
                });
            }
        } else {
            $uncachedQuery->whereNotNull('product_id')
                         ->with([
                             'card.prices' => function($q) {
                                 $q->latest('snapshot_at')->limit(1);
                             },
                             'card.rapidapiCard',
                             'card.cardmarketProduct.latestPriceQuote'
                         ]);
            // Filter by current game (only for TCGCSV)
            if ($currentGame) {
                $uncachedQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
            }
            
            // Apply rarity filter for TCGCSV
            if ($rarityFilter) {
                $uncachedQuery->whereHas('card', function($q) use ($rarityFilter) {
                    $q->where('rarity', $rarityFilter);
                });
            }
        }
        
        $uncachedItems = $uncachedQuery->get();
        
        foreach ($uncachedItems as $item) {
            // TCGDEX pricing
            if ($item->tcgdex_card_id && $item->tcgdexCard) {
                $pricing = $item->tcgdexCard->raw['pricing'] ?? null;
                if ($pricing && isset($pricing['cardmarket']['averageSellPrice'])) {
                    $priceEur = $pricing['cardmarket']['averageSellPrice'];
                    $totalValueEur += $priceEur * $item->quantity;
                    $totalValueUsd += ($priceEur * 1.10) * $item->quantity;
                }
                continue;
            }
            
            // TCGCSV pricing
            if ($item->product_id && $item->card) {
                // USD price from TCGPlayer
                $latestPrice = $item->card->prices->first();
                $marketPriceUsd = $latestPrice?->market_price ?? 0;
                
                if ($marketPriceUsd > 0) {
                    $totalValueUsd += $marketPriceUsd * $item->quantity;
                }
                
                // EUR price - Priority system
                $marketPriceEur = 0;
                
                // Priority 1: Cardmarket price quotes
                $cardmarketProduct = $item->card->cardmarketProduct;
                if ($cardmarketProduct) {
                    $latestQuote = $cardmarketProduct->latestPriceQuote;
                    if ($latestQuote && $latestQuote->trend > 0) {
                        $marketPriceEur = $latestQuote->trend;
                    } elseif ($latestQuote && $latestQuote->avg > 0) {
                        $marketPriceEur = $latestQuote->avg;
                    }
                }
                
                // Priority 2: Cardmarket EUR from tcgcsv_products
                if ($marketPriceEur === 0 && $item->card->cardmarket_price_eur && $item->card->cardmarket_price_eur > 0) {
                    $marketPriceEur = $item->card->cardmarket_price_eur;
                }
                
                // Priority 3: RapidAPI Cardmarket data
                if ($marketPriceEur === 0) {
                    $rapidapiCard = $item->card->rapidapiCard;
                    if ($rapidapiCard && isset($rapidapiCard->raw_data['prices']['cardmarket']['lowest_near_mint'])) {
                        $marketPriceEur = (float) $rapidapiCard->raw_data['prices']['cardmarket']['lowest_near_mint'];
                    }
                }
                
                if ($marketPriceEur > 0) {
                    $totalValueEur += $marketPriceEur * $item->quantity;
                }
            }
        }
        
        return [
            'total_value_usd' => round($totalValueUsd, 2),
            'total_value_eur' => round($totalValueEur, 2),
            'cards_with_prices_usd' => $cardsWithCachedPrices + $uncachedItems->count(),
            'cards_with_prices_eur' => $cardsWithCachedPrices + $uncachedItems->count(),
            'cached_items' => $cardsWithCachedPrices,
            'uncached_items' => $uncachedItems->count(),
        ];
    }

    /**
     * Upload a photo for a collection item (Premium only)
     */
    public function uploadPhoto(Request $request, UserCollection $collection)
    {
        // Authorization: must own the collection item
        if ($collection->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Authorization: must be premium
        if (!Gate::allows('uploadCardPhotos')) {
            return back()->with('error', __('photos.upload.not_allowed.title'));
        }

        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120', // 5MB max
        ]);

        $file = $request->file('photo');
        
        // Store in local storage (storage/app/private)
        $path = $file->store('user-card-photos/' . Auth::id(), 'local');
        
        // Create photo record
        $photo = \App\Models\UserCardPhoto::create([
            'user_id' => Auth::id(),
            'user_collection_id' => $collection->id,
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
        ]);

        return back()->with('success', __('photos.upload.success'));
    }

    /**
     * Serve a photo file (owner only)
     */
    public function servePhoto(\App\Models\UserCardPhoto $photo)
    {
        // Authorization: must own the photo
        if ($photo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$photo->path || !\Storage::disk('local')->exists($photo->path)) {
            abort(404, 'Photo not found.');
        }

        return response()->file(
            storage_path('app/private/' . $photo->path),
            ['Content-Type' => $photo->mime_type ?? 'image/jpeg']
        );
    }

    /**
     * Delete a photo (owner only)
     */
    public function deletePhoto(\App\Models\UserCardPhoto $photo)
    {
        // Authorization: must own the photo
        if ($photo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $photo->delete(); // Will also delete file via model event

        return back()->with('success', __('photos.delete.success'));
    }

    /**
     * Quick add card to collection (AJAX endpoint)
     */
    public function quickAdd(Request $request)
    {
        // Determine if this is TCGDEX or TCGCSV card
        $isTcgdex = $request->has('tcgdex_card_id');
        
        if ($isTcgdex) {
            $validated = $request->validate([
                'tcgdex_card_id' => 'required|integer|exists:tcgdx_cards,id',
                'quantity' => 'required|integer|min:1|max:100',
                'condition' => 'required|string|in:M,NM,LP,MP,HP,D',
            ]);
        } else {
            $validated = $request->validate([
                'card_id' => 'required|integer|exists:tcgcsv_products,product_id',
                'quantity' => 'required|integer|min:1|max:100',
                'condition' => 'required|string|in:M,NM,LP,MP,HP,D',
            ]);
        }

        try {
            // Check if user already has this card
            $query = UserCollection::where('user_id', Auth::id())
                ->where('condition', $validated['condition']);
            
            if ($isTcgdex) {
                $query->where('tcgdex_card_id', $validated['tcgdex_card_id']);
            } else {
                $query->where('product_id', $validated['card_id']);
            }
            
            $existingCard = $query->first();

            if ($existingCard) {
                // Update quantity if card already exists
                $existingCard->quantity += $validated['quantity'];
                $existingCard->save();
                
                return response()->json([
                    'success' => true,
                    'message' => __('dashboard.card_added_successfully'),
                    'action' => 'updated',
                    'new_quantity' => $existingCard->quantity,
                ]);
            } else {
                // Create new collection entry
                $data = [
                    'user_id' => Auth::id(),
                    'quantity' => $validated['quantity'],
                    'condition' => $validated['condition'],
                    'is_foil' => false, // Default to non-foil for quick add
                ];
                
                if ($isTcgdex) {
                    $data['tcgdex_card_id'] = $validated['tcgdex_card_id'];
                } else {
                    $data['product_id'] = $validated['card_id'];
                }
                
                UserCollection::create($data);

                return response()->json([
                    'success' => true,
                    'message' => __('dashboard.card_added_successfully'),
                    'action' => 'created',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Quick add card error', [
                'user_id' => Auth::id(),
                'card_id' => $validated['card_id'] ?? null,
                'tcgdex_card_id' => $validated['tcgdex_card_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('dashboard.error_adding_card'),
            ], 500);
        }
    }
}
