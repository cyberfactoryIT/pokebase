<?php

namespace App\Http\Controllers;

use App\Models\Cmapi\CmapiSet;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class CmapiSetController extends Controller
{
    /**
     * Display sets index page (for Lorcana/One Piece)
     */
    public function index(Request $request): View
    {
        $game = $request->route('game') ?? 'lorcana'; // Default to lorcana
        
        return view('cmapi.sets.index', compact('game'));
    }

    /**
     * AJAX search endpoint for sets
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'nullable|string|max:100',
            'page' => 'integer|min:1',
            'game' => 'required|in:lorcana,onepiece',
        ]);

        $game = $validated['game'];
        $query = CmapiSet::where('game', $game)->withCount('cards');

        // Default order by release date desc
        $query->orderBy('release_date', 'desc');

        // Search filter
        if (!empty($validated['query'])) {
            $searchTerm = $validated['query'];
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('cmapi_episode', 'like', "%{$searchTerm}%");
            });
        }

        // Paginate
        $sets = $query->paginate(24);

        // Map results
        $data = $sets->map(function($set) {
            return [
                'set_cmapi_id' => $set->set_cmapi_id,
                'cmapi_episode' => $set->cmapi_episode,
                'name' => $set->name,
                'release_date' => $set->release_date ? $set->release_date->format('Y-m-d') : null,
                'card_count' => $set->card_count,
                'cards_count' => $set->cards_count,
                'logo_url' => $set->logo_url,
                'game' => $set->game,
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
     * Display a specific set with its cards
     */
    public function show(Request $request, string $game, int $episodeId): View
    {
        $set = CmapiSet::where('game', $game)
            ->where('cmapi_episode', $episodeId)
            ->withCount('cards')
            ->firstOrFail();

        return view('cmapi.sets.show', compact('set', 'game'));
    }

    /**
     * AJAX search endpoint for cards within a set
     */
    public function cardsSearch(Request $request, string $game, int $episodeId): JsonResponse
    {
        $set = CmapiSet::where('game', $game)
            ->where('cmapi_episode', $episodeId)
            ->firstOrFail();

        $validated = $request->validate([
            'query' => 'nullable|string|max:100',
            'page' => 'integer|min:1',
            'rarity' => 'nullable|string',
            'card_type' => 'nullable|string',
            'ink_color' => 'nullable|string', // For Lorcana
        ]);

        $query = $set->cards();

        // Search filter
        if (!empty($validated['query'])) {
            $searchTerm = $validated['query'];
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('number', 'like', "%{$searchTerm}%");
            });
        }

        // Rarity filter
        if (!empty($validated['rarity'])) {
            $query->where('rarity', $validated['rarity']);
        }

        // Card type filter (Lorcana: Character, Action, Item, Location)
        if (!empty($validated['card_type'])) {
            $query->where('card_type', $validated['card_type']);
        }

        // Ink color filter (Lorcana only)
        if (!empty($validated['ink_color'])) {
            $query->where('ink_color', $validated['ink_color']);
        }

        // Order by card number
        $query->orderByRaw('CAST(number AS UNSIGNED), number');

        // Paginate
        $cards = $query->paginate(48);

        // Map results
        $data = $cards->map(function($card) {
            return [
                'cmapi_id' => $card->cmapi_id,
                'name' => $card->name,
                'number' => $card->number,
                'rarity' => $card->rarity,
                'image_small_url' => $card->image_small_url,
                'image_large_url' => $card->image_large_url,
                'price_eur' => $card->price_eur,
                'price_usd' => $card->price_usd,
                // Lorcana-specific
                'ink_cost' => $card->ink_cost,
                'card_type' => $card->card_type,
                'lore_value' => $card->lore_value,
                'ink_color' => $card->ink_color,
                // One Piece-specific
                'cost' => $card->cost,
                'power' => $card->power,
                'counter' => $card->counter,
                'color' => $card->color,
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

    /**
     * Show individual card detail page
     */
    public function showCard(Request $request, string $game, string $cardId): View
    {
        $card = \App\Models\Cmapi\CmapiCard::where('cmapi_id', $cardId)
            ->where('game', $game)
            ->with('set')
            ->firstOrFail();

        // Add user interaction flags if authenticated
        if (auth()->check()) {
            $userId = auth()->id();
            
            $card->is_liked = \DB::table('user_likes')
                ->where('user_id', $userId)
                ->where('cmapi_card_id', $card->cmapi_id)
                ->exists();
                
            $card->is_in_wishlist = \DB::table('user_wishlist_items')
                ->where('user_id', $userId)
                ->where('cmapi_card_id', $card->cmapi_id)
                ->exists();
                
            $card->is_watched = \DB::table('user_watch_items')
                ->where('user_id', $userId)
                ->where('cmapi_card_id', $card->cmapi_id)
                ->exists();
        } else {
            $card->is_liked = false;
            $card->is_in_wishlist = false;
            $card->is_watched = false;
        }

        return view('cmapi.cards.show', compact('card', 'game'));
    }
}
