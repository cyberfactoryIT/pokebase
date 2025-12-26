<?php

namespace App\Http\Controllers;

use App\Models\UserCollection;
use App\Models\TcgcsvProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        
        $query = UserCollection::where('user_id', $userId)
            ->with('card.group');
            
        // Filter by current game
        if ($currentGame) {
            $query->whereHas('card', function($q) use ($currentGame) {
                $q->where('game_id', $currentGame->id);
            });
        }
        
        $collection = $query->orderBy('created_at', 'desc')
            ->paginate(24);

        // Basic stats (filtered by game)
        $stats = [
            'total_cards' => $this->getUserCardCount($userId, $currentGame),
            'unique_cards' => $this->getUserUniqueCardCount($userId, $currentGame),
            'foil_cards' => $this->getUserFoilCardCount($userId, $currentGame),
        ];

        // Top 3 interesting stats for header
        $topStats = $this->getTopStats($userId, $currentGame);
        
        // Detailed statistics for stats tab
        $detailedStats = $this->getDetailedStats($userId, $currentGame);

        return view('collection.index', compact('collection', 'stats', 'topStats', 'detailedStats'));
    }
    
    private function getUserCardCount($userId, $currentGame)
    {
        $query = UserCollection::where('user_id', $userId);
        if ($currentGame) {
            $query->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
        }
        return $query->sum('quantity');
    }
    
    private function getUserUniqueCardCount($userId, $currentGame)
    {
        $query = UserCollection::where('user_id', $userId);
        if ($currentGame) {
            $query->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
        }
        return $query->count();
    }
    
    private function getUserFoilCardCount($userId, $currentGame)
    {
        $query = UserCollection::where('user_id', $userId)->where('is_foil', true);
        if ($currentGame) {
            $query->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
        }
        return $query->sum('quantity');
    }
    
    /**
     * Get top 3 interesting stats for header
     */
    private function getTopStats($userId, $currentGame): array
    {
        // 1. Rarity distribution (most interesting)
        $rarityQuery = UserCollection::where('user_id', $userId)
            ->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.product_id')
            ->selectRaw('tcgcsv_products.rarity, COUNT(*) as count, SUM(user_collection.quantity) as total_quantity')
            ->groupBy('tcgcsv_products.rarity')
            ->orderBy('count', 'desc');
            
        if ($currentGame) {
            $rarityQuery->where('tcgcsv_products.game_id', $currentGame->id);
        }
        $rarityDistribution = $rarityQuery->get();
        
        // 2. Foil percentage
        $totalCards = $this->getUserCardCount($userId, $currentGame);
        $foilCards = $this->getUserFoilCardCount($userId, $currentGame);
        $foilPercentage = $totalCards > 0 ? round(($foilCards / $totalCards) * 100, 1) : 0;
        
        // 3. Set completion (top set)
        $topSetQuery = UserCollection::where('user_id', $userId)
            ->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.product_id')
            ->join('tcgcsv_groups', 'tcgcsv_products.group_id', '=', 'tcgcsv_groups.group_id')
            ->selectRaw('tcgcsv_groups.group_id, tcgcsv_groups.name, COUNT(DISTINCT user_collection.product_id) as owned_count')
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
    private function getDetailedStats($userId, $currentGame): array
    {
        // Condition distribution
        $conditionQuery = UserCollection::where('user_id', $userId)
            ->selectRaw('`condition`, COUNT(*) as count, SUM(quantity) as total_quantity')
            ->groupBy('condition');
        if ($currentGame) {
            $conditionQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
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
        $cardsWithNotes = $cardsWithNotesQuery->count();
        
        // Duplicate cards (quantity > 1)
        $duplicateCardsQuery = UserCollection::where('user_id', $userId)
            ->where('quantity', '>', 1);
        if ($currentGame) {
            $duplicateCardsQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
        }
        $duplicateCards = $duplicateCardsQuery->count();
        
        // Set statistics
        $setStatsQuery = UserCollection::where('user_id', $userId)
            ->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.product_id')
            ->join('tcgcsv_groups', 'tcgcsv_products.group_id', '=', 'tcgcsv_groups.group_id')
            ->selectRaw('COUNT(DISTINCT tcgcsv_groups.group_id) as total_sets');
        if ($currentGame) {
            $setStatsQuery->where('tcgcsv_groups.game_id', $currentGame->id);
        }
        $setStats = $setStatsQuery->first();
        
        // Top 5 sets by completion
        $topSetsQuery = UserCollection::where('user_id', $userId)
            ->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.product_id')
            ->join('tcgcsv_groups', 'tcgcsv_products.group_id', '=', 'tcgcsv_groups.group_id')
            ->selectRaw('tcgcsv_groups.group_id, tcgcsv_groups.name, COUNT(DISTINCT user_collection.product_id) as owned_count')
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
        
        // Timeline - cards added by month (last 6 months)
        $timelineQuery = UserCollection::where('user_id', $userId)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'asc');
        if ($currentGame) {
            $timelineQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
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

        // Check if card already exists with same condition/foil
        $existing = UserCollection::where('user_id', Auth::id())
            ->where('product_id', $validated['product_id'])
            ->where('condition', $validated['condition'] ?? null)
            ->where('is_foil', $validated['is_foil'] ?? false)
            ->first();

        if ($existing) {
            // Increment quantity
            $existing->increment('quantity', $validated['quantity'] ?? 1);
            $message = 'Card quantity updated in your collection!';
        } else {
            // Create new entry
            UserCollection::create([
                'user_id' => Auth::id(),
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'] ?? 1,
                'condition' => $validated['condition'] ?? null,
                'is_foil' => $validated['is_foil'] ?? false,
                'notes' => $validated['notes'] ?? null,
            ]);
            $message = 'Card added to your collection!';
        }

        return back()->with('success', $message);
    }

    /**
     * Remove a card from collection
     */
    public function remove(UserCollection $collectionItem): RedirectResponse
    {
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
    public function update(Request $request, UserCollection $collectionItem): RedirectResponse
    {
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
}
