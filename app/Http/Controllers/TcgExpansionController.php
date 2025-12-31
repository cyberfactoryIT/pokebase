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
            'tab' => 'nullable|string|in:all,top,upcoming',
        ]);

        $tab = $validated['tab'] ?? 'all';

        $query = TcgcsvGroup::query()
            ->where('category_id', $currentGame->id)
            ->withCount('products');

        // Search filter
        if (!empty($validated['query'])) {
            $searchTerm = $validated['query'];
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('abbreviation', 'like', "%{$searchTerm}%");
            });
        }

        $today = now()->toDateString();
        
        // Tab-specific filtering
        if ($tab === 'upcoming') {
            // Only future releases
            $query->where('published_on', '>', $today)
                  ->orderBy('published_on', 'ASC');
            $upcoming = collect();
        } elseif ($tab === 'top') {
            // Top expansions: with logo and value, ordered by date descending
            $query->where(function($q) use ($today) {
                $q->whereNull('tcgcsv_groups.published_on')
                  ->orWhere('tcgcsv_groups.published_on', '<=', $today);
            })
            ->whereNotNull('tcgcsv_groups.logo_url');
            
            // Join with rapidapi_episodes to filter by value
            $query->join('rapidapi_episodes', function($join) {
                $join->on('tcgcsv_groups.abbreviation', '=', 'rapidapi_episodes.code');
            })
            ->where('rapidapi_episodes.cardmarket_total_value', '>', 0)
            ->select('tcgcsv_groups.*')
            ->addSelect('rapidapi_episodes.cardmarket_total_value')
            ->addSelect('rapidapi_episodes.cards_printed_total')
            ->orderByRaw('tcgcsv_groups.published_on IS NULL, tcgcsv_groups.published_on DESC');
            
            $upcoming = collect();
        } else {
            // 'all' tab - current/past releases
            // Get upcoming releases for banner
            $upcomingQuery = clone $query;
            $upcoming = $upcomingQuery
                ->where('published_on', '>', $today)
                ->orderBy('published_on', 'ASC')
                ->get()
                ->map(function($expansion) {
                    return [
                        'group_id' => $expansion->group_id,
                        'name' => $expansion->name,
                        'abbreviation' => $expansion->abbreviation,
                        'published_on' => $expansion->published_on ? $expansion->published_on->format('Y-m-d') : null,
                    ];
                });
            
            // Filter main query
            $query->where(function($q) use ($today) {
                $q->whereNull('published_on')
                  ->orWhere('published_on', '<=', $today);
            });
            $query->orderByRaw('published_on IS NULL, published_on DESC');
        }

        // Paginate
        $expansions = $query->paginate(25);

        return response()->json([
            'upcoming' => $upcoming,
            'data' => $expansions->map(function($expansion) {
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
            }),
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

        return response()->json([
            'data' => $cards->map(function($card) {
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
