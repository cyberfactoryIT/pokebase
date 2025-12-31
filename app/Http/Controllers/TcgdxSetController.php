<?php

namespace App\Http\Controllers;

use App\Models\Tcgdx\TcgdxSet;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class TcgdxSetController extends Controller
{
    /**
     * Display sets index page
     */
    public function index(Request $request): View
    {
        return view('tcgdx.sets.index');
    }

    /**
     * AJAX search endpoint for sets
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'nullable|string|max:100',
            'page' => 'integer|min:1',
            'tab' => 'nullable|in:all,series,coming-soon',
            'series' => 'nullable|string',
        ]);

        $query = TcgdxSet::query()->withCount('cards');

        $tab = $validated['tab'] ?? 'all';

        // Tab filters
        if ($tab === 'coming-soon') {
            $query->where('release_date', '>', now());
            $query->orderBy('release_date', 'asc');
        } elseif ($tab === 'series' && !empty($validated['series'])) {
            $query->where('series', $validated['series']);
            $query->orderBy('release_date', 'desc');
        } else {
            // All: already released or no date
            $query->where(function($q) {
                $q->whereNull('release_date')
                  ->orWhere('release_date', '<=', now());
            });
            $query->orderBy('release_date', 'desc');
        }

        // Search filter
        if (!empty($validated['query'])) {
            $searchTerm = $validated['query'];
            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw('JSON_EXTRACT(name, "$.en") LIKE ?', ["%{$searchTerm}%"])
                  ->orWhere('tcgdex_id', 'like', "%{$searchTerm}%");
            });
        }

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
                'cards_count' => $set->cards_count,
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
    public function show(Request $request, string $setId): View
    {
        $set = TcgdxSet::where('tcgdex_id', $setId)
            ->withCount('cards')
            ->firstOrFail();

        return view('tcgdx.sets.show', compact('set'));
    }

    /**
     * AJAX search endpoint for cards within a set
     */
    public function cardsSearch(Request $request, string $setId): JsonResponse
    {
        $set = TcgdxSet::where('tcgdex_id', $setId)->firstOrFail();

        $validated = $request->validate([
            'query' => 'nullable|string|max:100',
            'page' => 'integer|min:1',
            'rarity' => 'nullable|string',
            'type' => 'nullable|string',
        ]);

        $query = $set->cards();

        // Search filter
        if (!empty($validated['query'])) {
            $searchTerm = $validated['query'];
            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw('JSON_EXTRACT(name, "$.en") LIKE ?', ["%{$searchTerm}%"])
                  ->orWhere('local_id', 'like', "%{$searchTerm}%")
                  ->orWhere('illustrator', 'like', "%{$searchTerm}%");
            });
        }

        // Rarity filter
        if (!empty($validated['rarity'])) {
            $query->where('rarity', $validated['rarity']);
        }

        // Type filter
        if (!empty($validated['type'])) {
            $query->whereRaw('JSON_CONTAINS(types, ?)', [json_encode($validated['type'])]);
        }

        // Order by local_id (card number)
        $query->orderByRaw('CAST(local_id AS UNSIGNED), local_id');

        // Paginate
        $cards = $query->paginate(48);

        // Map results
        $data = $cards->map(function($card) {
            return [
                'tcgdex_id' => $card->tcgdex_id,
                'local_id' => $card->local_id,
                'number' => $card->number,
                'name' => $card->getLocalizedName(),
                'rarity' => $card->rarity,
                'hp' => $card->hp,
                'types' => $card->types,
                'supertype' => $card->supertype,
                'image_small_url' => $card->image_small_url,
                'image_large_url' => $card->image_large_url,
                'hd_image_url' => $card->image_large_url ? $card->image_large_url . '/high.webp' : null,
                'illustrator' => $card->illustrator,
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
