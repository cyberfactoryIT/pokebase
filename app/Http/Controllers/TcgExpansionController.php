<?php

namespace App\Http\Controllers;

use App\Models\TcgcsvGroup;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class TcgExpansionController extends Controller
{
    /**
     * Display expansions index page
     */
    public function index(Request $request): View
    {
        return view('tcg.expansions.index');
    }

    /**
     * AJAX search endpoint for expansions
     */
    public function search(Request $request): JsonResponse
    {
        $currentGame = $request->attributes->get('currentGame');
        
        // If no current game, return empty
        if (!$currentGame) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 25,
                    'total' => 0,
                ],
            ]);
        }

        $validated = $request->validate([
            'query' => 'nullable|string|max:100',
            'page' => 'integer|min:1',
            'tab' => 'nullable|in:all,top,coming-soon',
        ]);

        $query = TcgcsvGroup::query()
            ->where('game_id', $currentGame->id)
            ->withCount('products');

        $tab = $validated['tab'] ?? 'all';

        // Tab filters
        if ($tab === 'coming-soon') {
            // Future releases only
            $query->where('published_on', '>', now());
            $query->orderBy('published_on', 'asc');
        } elseif ($tab === 'top') {
            // Only published expansions with Cardmarket value
            $query->where(function($q) {
                $q->whereNull('published_on')
                  ->orWhere('published_on', '<=', now());
            });
        } else {
            // All: already released or no date
            $query->where(function($q) {
                $q->whereNull('published_on')
                  ->orWhere('published_on', '<=', now());
            });
        }

        // Search filter
        if (!empty($validated['query'])) {
            $searchTerm = $validated['query'];
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('abbreviation', 'like', "%{$searchTerm}%");
            });
        }

        // Default order for 'all' and 'top' tabs (will be overridden for top after getting cardmarket values)
        if ($tab !== 'coming-soon') {
            $query->orderByRaw('published_on IS NULL, published_on DESC');
        }

        // Paginate
        $expansions = $query->paginate(25);

        // Map results and add Cardmarket data
        $data = $expansions->map(function($expansion) {
            // Get Cardmarket value from rapidapi_episodes if available
            $rapidapiEpisode = \DB::table('rapidapi_episodes')
                ->where('code', $expansion->abbreviation)
                ->first();
            
            return [
                'group_id' => $expansion->group_id,
                'name' => $expansion->name,
                'abbreviation' => $expansion->abbreviation,
                'published_on' => $expansion->published_on ? $expansion->published_on->format('Y-m-d') : null,
                'products_count' => $expansion->products_count,
                'color' => $this->getExpansionColor($expansion->group_id),
                'logo_url' => $expansion->logo_url,
                'cardmarket_value' => $rapidapiEpisode->cardmarket_total_value ?? 0,
                'cards_printed' => $rapidapiEpisode->cards_printed_total ?? $expansion->products_count,
            ];
        });

        // Sort by Cardmarket value for 'top' tab
        if ($tab === 'top') {
            $data = $data->sortByDesc('cardmarket_value')->values();
        }

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $expansions->currentPage(),
                'last_page' => $expansions->lastPage(),
                'per_page' => $expansions->perPage(),
                'total' => $expansions->total(),
            ],
        ]);
    }

    /**
     * Show expansion detail with cards
     */
    public function show(Request $request, int $groupId): View
    {
        $currentGame = $request->attributes->get('currentGame');
        
        $expansion = TcgcsvGroup::where('group_id', $groupId)
            ->where('game_id', $currentGame ? $currentGame->id : null)
            ->withCount('products')
            ->firstOrFail();

        return view('tcg.expansions.show', compact('expansion'));
    }

    /**
     * AJAX search endpoint for cards within an expansion
     */
    public function cardsSearch(Request $request, int $groupId): JsonResponse
    {
        $currentGame = $request->attributes->get('currentGame');
        
        $validated = $request->validate([
            'query' => 'nullable|string|max:100',
            'page' => 'integer|min:1',
        ]);

        // Verify expansion exists and belongs to current game
        $expansion = TcgcsvGroup::where('group_id', $groupId)
            ->where('game_id', $currentGame ? $currentGame->id : null)
            ->firstOrFail();

        $query = $expansion->products()
            ->where('game_id', $currentGame->id)
            ->with('rapidapiCard');

        // Search filter
        if (!empty($validated['query'])) {
            $searchTerm = $validated['query'];
            $query->where('name', 'like', "%{$searchTerm}%");
        }

        // Progressive number sorting
        // card_number might be "3/102" or just "3" or have prefixes like "SV003"
        // Sort by the leading numeric part, then by full string
        $query->orderByRaw('
            CAST(
                CASE 
                    WHEN card_number REGEXP "^[0-9]+" 
                    THEN REGEXP_SUBSTR(card_number, "^[0-9]+")
                    ELSE 999999
                END 
                AS UNSIGNED
            ) ASC, 
            card_number ASC
        ');

        // Paginate
        $cards = $query->paginate(50);

        // Load user interaction states efficiently (no N+1)
        $userInteractions = [];
        if ($user = auth()->user()) {
            $productIds = $cards->pluck('product_id')->toArray();
            
            // Get all liked products in one query
            $likedIds = \DB::table('user_likes')
                ->where('user_id', $user->id)
                ->whereIn('product_id', $productIds)
                ->pluck('product_id')
                ->toArray();
            
            // Get all wishlist products in one query
            $wishlistIds = \DB::table('user_wishlist_items')
                ->where('user_id', $user->id)
                ->whereIn('product_id', $productIds)
                ->pluck('product_id')
                ->toArray();
            
            // Get all watched products in one query
            $watchIds = \DB::table('user_watch_items')
                ->where('user_id', $user->id)
                ->whereIn('product_id', $productIds)
                ->pluck('product_id')
                ->toArray();
            
            // Get all products in user's collection
            $collectionIds = \DB::table('user_collection')
                ->where('user_id', $user->id)
                ->whereIn('product_id', $productIds)
                ->pluck('product_id')
                ->toArray();
            
            // Get all products in user's decks
            $deckIds = \DB::table('deck_cards')
                ->join('decks', 'deck_cards.deck_id', '=', 'decks.id')
                ->where('decks.user_id', $user->id)
                ->whereIn('deck_cards.product_id', $productIds)
                ->pluck('deck_cards.product_id')
                ->toArray();
            
            $userInteractions = [
                'liked' => $likedIds,
                'wishlist' => $wishlistIds,
                'watched' => $watchIds,
                'collection' => $collectionIds,
                'deck' => $deckIds,
            ];
        }

        return response()->json([
            'data' => $cards->map(function($card) use ($userInteractions) {
                // Get HD image URL from RapidAPI if available
                $hdImageUrl = $card->rapidapiCard && $card->rapidapiCard->image_url 
                    ? $card->rapidapiCard->image_url 
                    : $card->hd_image_url;
                    
                return [
                    'product_id' => $card->product_id,
                    'name' => $card->name,
                    'card_number' => $card->card_number,
                    'image_url' => $this->getCardImage($card),
                    'hd_image_url' => $hdImageUrl,
                    'hp' => $card->hp,
                    'is_liked' => !empty($userInteractions) && in_array($card->product_id, $userInteractions['liked']),
                    'is_wishlist' => !empty($userInteractions) && in_array($card->product_id, $userInteractions['wishlist']),
                    'is_watched' => !empty($userInteractions) && in_array($card->product_id, $userInteractions['watched']),
                    'is_in_collection' => !empty($userInteractions) && in_array($card->product_id, $userInteractions['collection']),
                    'is_in_deck' => !empty($userInteractions) && in_array($card->product_id, $userInteractions['deck']),
                ];
            }),
            'meta' => [
                'current_page' => $cards->currentPage(),
                'last_page' => $cards->lastPage(),
                'per_page' => $cards->perPage(),
                'total' => $cards->total(),
            ],
        ]);
    }

    /**
     * Get card image URL with fallbacks
     */
    private function getCardImage($card): ?string
    {
        // Try direct field
        if (!empty($card->image_url)) {
            return $card->image_url;
        }

        // Try raw JSON variations
        if (!empty($card->raw)) {
            $raw = $card->raw;
            
            // Common variations
            if (!empty($raw['imageUrl'])) return $raw['imageUrl'];
            if (!empty($raw['image_url'])) return $raw['image_url'];
            if (!empty($raw['images']['large'])) return $raw['images']['large'];
            if (!empty($raw['images']['small'])) return $raw['images']['small'];
            if (!empty($raw['image'])) return $raw['image'];
        }

        return null;
    }

    /**
     * Generate a consistent color for expansion badge based on group_id
     */
    private function getExpansionColor(int $groupId): string
    {
        $colors = [
            'bg-blue-500',
            'bg-purple-500',
            'bg-pink-500',
            'bg-red-500',
            'bg-orange-500',
            'bg-yellow-500',
            'bg-green-500',
            'bg-teal-500',
            'bg-cyan-500',
            'bg-indigo-500',
        ];
        
        return $colors[$groupId % count($colors)];
    }
}
