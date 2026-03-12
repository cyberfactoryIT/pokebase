<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CardSearchRequest;
use App\Models\Cmapi\CmapiCard;
use App\Models\Cmapi\CmapiSet;
use App\Models\TcgcsvProduct;
use App\Models\UserCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Card Search API Controller
 * 
 * Provides global card search across all sets/expansions for typeahead suggestions.
 * Game-agnostic implementation with performance guards.
 */
class CardSearchController extends Controller
{
    /**
     * Search for cards globally across all sets
     * 
     * GET /api/search/cards?q=charizard&limit=12
     * 
     * Response fields:
     * - product_id: int
     * - name: string
     * - card_number: string|null
     * - group_id: int
     * - group_name: string
     * - group_published_on: string|null (ISO 8601 date)
     * - image_url: string|null
     * 
     * @param CardSearchRequest $request
     * @return JsonResponse
     */
    public function index(CardSearchRequest $request): JsonResponse
    {
        try {
            $query = $request->getQuery();
            $limit = $request->getLimit();
            $collectionOnly = filter_var($request->input('collection_only', false), FILTER_VALIDATE_BOOLEAN);

            // Get catalog backend from explicit parameter, or use helper to determine from context
            $catalogBackend = $request->input('backend');
            
            if (!$catalogBackend) {
                // Fall back to helper (handles session, user default, and route context)
                $catalogBackend = catalog_backend();
            }
            
            // Log for debugging
            Log::info('Card search API called', [
                'query' => $query,
                'backend' => $catalogBackend,
                'url' => $request->fullUrl(),
                'referer' => $request->header('referer'),
            ]);

            // Get current game ID for filtering (used in TCGCSV and CMAPI search)
            $gameId = session('current_game_id');
            if (!$gameId && Auth::check()) {
                $gameId = Auth::user()->default_game_id;
            }
            
            // Get game code for CMAPI (uses slug/code, not numeric ID)
            $gameCode = null;
            if ($catalogBackend === 'cmapi') {
                // Prefer explicit game passed from frontend (e.g. current cmapi game slug)
                $explicitGame = $request->input('game');
                if ($explicitGame) {
                    $gameCode = $explicitGame;
                } elseif ($gameId) {
                    // Fallback: derive from current game in session/user
                    $game = \App\Models\Game::find($gameId);
                    $gameCode = $game ? $game->code : null;
                }
            }

            // Search based on catalog backend
            if ($catalogBackend === 'tcgdex') {
                // Search in TCGDEX tables
                return $this->searchTcgdex($query, $limit, $collectionOnly);
            } elseif ($catalogBackend === 'cmapi') {
                // Search in CMAPI tables (Lorcana, One Piece)
                return $this->searchCmapi($query, $limit, $collectionOnly, $gameCode);
            } else {
                // Search in TCGCSV tables
                return $this->searchTcgcsv($query, $limit, $collectionOnly, $gameId);
            }

        } catch (\Exception $e) {
            // Log unexpected errors with context
            Log::error('Card search API error', [
                'query' => $query ?? null,
                'limit' => $limit ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'An unexpected error occurred while searching cards',
            ], 500);
        }
    }

    /**
     * Search in TCGCSV database
     */
    private function searchTcgcsv(string $query, int $limit, bool $collectionOnly, ?int $gameId): JsonResponse
    {
        // Escape LIKE wildcards to prevent injection
        $escapedQuery = $this->escapeLikeWildcards($query);
        
        // Build search query with prefix/contains ranking
        $results = TcgcsvProduct::query()
            ->select([
                'tcgcsv_products.product_id',
                'tcgcsv_products.name',
                'tcgcsv_products.card_number',
                DB::raw('SUBSTRING_INDEX(tcgcsv_products.card_number, "/", 1) as card_number_only'),
                DB::raw('CAST(SUBSTRING_INDEX(tcgcsv_products.card_number, "/", -1) AS UNSIGNED) as set_total'),
                'tcgcsv_products.group_id',
                'tcgcsv_groups.name as group_name',
                'tcgcsv_groups.abbreviation as group_code',
                'tcgcsv_groups.published_on as group_published_on',
                'tcgcsv_products.image_url',
            ])
            ->leftJoin('tcgcsv_groups', 'tcgcsv_products.group_id', '=', 'tcgcsv_groups.group_id');
        
        // Filter by game if set
        if ($gameId) {
            $results->where('tcgcsv_products.game_id', $gameId);
        }
        
        // Filter by collection if requested
        if ($collectionOnly && Auth::check()) {
            $userId = Auth::id();
            $results->whereIn('tcgcsv_products.product_id', function($query) use ($userId) {
                $query->select('product_id')
                    ->from('user_collection')
                    ->where('user_id', $userId)
                    ->whereNotNull('product_id'); // Only TCGCSV cards
            });
        }
        
        // Search by:
        // - Card name
        // - Card number (like "1/102", "001")
        // - Set abbreviation (like "BS", "SSH")
        // - Set name
        $results->where(function($q) use ($escapedQuery) {
                $q->where('tcgcsv_products.name', 'LIKE', "%{$escapedQuery}%")
                  ->orWhere('tcgcsv_products.card_number', 'LIKE', "%{$escapedQuery}%")
                  ->orWhere('tcgcsv_groups.abbreviation', 'LIKE', "%{$escapedQuery}%")
                  ->orWhere('tcgcsv_groups.name', 'LIKE', "%{$escapedQuery}%");
            })
            ->orderByRaw(
                'CASE 
                    WHEN tcgcsv_products.card_number = ? THEN 0
                    WHEN tcgcsv_groups.abbreviation = ? THEN 1
                    WHEN tcgcsv_products.name LIKE ? THEN 2 
                    WHEN tcgcsv_products.card_number LIKE ? THEN 3
                    WHEN tcgcsv_groups.name LIKE ? THEN 4
                    ELSE 5 
                END',
                [$escapedQuery, $escapedQuery, "{$escapedQuery}%", "{$escapedQuery}%", "{$escapedQuery}%"]
            )
            ->orderByRaw('tcgcsv_groups.published_on IS NULL')
            ->orderBy('tcgcsv_groups.published_on', 'DESC')
            ->orderBy('tcgcsv_products.card_number', 'ASC')
            ->orderBy('tcgcsv_products.id', 'ASC')
            ->limit($limit);
            
        // Execute query
        $cards = $results->get();

        // Format response
        $formatted = $cards->map(function ($card) {
            return [
                'backend' => 'tcgcsv',
                'product_id' => $card->product_id,
                'name' => $card->name,
                'card_number' => $card->card_number_only,
                'set_total' => $card->set_total,
                'group_id' => $card->group_id,
                'group_code' => $card->group_code,
                'set_name' => $card->group_name,
                'group_name' => $card->group_name,
                'group_published_on' => $card->group_published_on 
                    ? (new \DateTime($card->group_published_on))->format('Y-m-d')
                    : null,
                'image_url' => $card->image_url,
            ];
        });

        return response()->json($formatted);
    }

    /**
     * Search in TCGDEX database
     */
    private function searchTcgdex(string $query, int $limit, bool $collectionOnly): JsonResponse
    {
        // Escape LIKE wildcards to prevent injection
        $escapedQuery = $this->escapeLikeWildcards($query);
        
        // Build search query
        $results = \App\Models\Tcgdx\TcgdxCard::query()
            ->select([
                'tcgdx_cards.id as tcgdex_card_id',
                'tcgdx_cards.tcgdex_id',
                'tcgdx_cards.name',
                'tcgdx_cards.local_id as card_number',
                'tcgdx_cards.set_tcgdx_id',
                'tcgdx_sets.name as set_name',
                'tcgdx_sets.tcgdex_id as set_code',
                'tcgdx_sets.card_count_official as set_total',
                'tcgdx_cards.image_small_url as image_url',
            ])
            ->leftJoin('tcgdx_sets', 'tcgdx_cards.set_tcgdx_id', '=', 'tcgdx_sets.id');
        
        // Filter by collection if requested
        if ($collectionOnly && Auth::check()) {
            $userId = Auth::id();
            $results->whereIn('tcgdx_cards.id', function($query) use ($userId) {
                $query->select('tcgdex_card_id')
                    ->from('user_collection')
                    ->where('user_id', $userId)
                    ->whereNotNull('tcgdex_card_id'); // Only TCGDEX cards
            });
        }
        
        // Search by:
        // - Card name (English from JSON)
        // - Card number (local_id like "1", "001") 
        // - Visible lookup key (visible_lookup_key like "BASE1 028/64")
        // - Full card ID (tcgdex_id like "base1-1")
        // - Set code (tcgdex_sets.tcgdex_id like "base1", "swsh1")
        // - Set name (English from JSON)
        $results->where(function($q) use ($escapedQuery) {
                $q->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(tcgdx_cards.name, "$.en"))) LIKE LOWER(?)', ["%{$escapedQuery}%"])
                  ->orWhere('tcgdx_cards.local_id', 'LIKE', "%{$escapedQuery}%")
                  ->orWhere('tcgdx_cards.visible_lookup_key', 'LIKE', "%{$escapedQuery}%")
                  ->orWhere('tcgdx_cards.tcgdex_id', 'LIKE', "%{$escapedQuery}%")
                  ->orWhere('tcgdx_sets.tcgdex_id', 'LIKE', "%{$escapedQuery}%")
                  ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(tcgdx_sets.name, "$.en"))) LIKE LOWER(?)', ["%{$escapedQuery}%"]);
            })
            ->orderByRaw(
                'CASE 
                    WHEN tcgdx_cards.visible_lookup_key = ? THEN 0
                    WHEN tcgdx_cards.local_id = ? THEN 1
                    WHEN tcgdx_sets.tcgdex_id = ? THEN 2
                    WHEN LOWER(JSON_UNQUOTE(JSON_EXTRACT(tcgdx_cards.name, "$.en"))) LIKE LOWER(?) THEN 3 
                    WHEN tcgdx_cards.local_id LIKE ? THEN 4
                    WHEN LOWER(JSON_UNQUOTE(JSON_EXTRACT(tcgdx_sets.name, "$.en"))) LIKE LOWER(?) THEN 5
                    ELSE 6 
                END',
                [$escapedQuery, $escapedQuery, $escapedQuery, "{$escapedQuery}%", "{$escapedQuery}%", "{$escapedQuery}%"]
            )
            ->orderBy('tcgdx_cards.id', 'DESC')
            ->limit($limit);
            
        // Execute query
        $cards = $results->get();

        // Format response (compatible with frontend expectations)
        $formatted = $cards->map(function ($card) {
            // Extract English name from JSON field
            $name = $card->name;
            if (is_string($name)) {
                $name = json_decode($name, true);
            }
            $nameEn = is_array($name) ? ($name['en'] ?? $name['fr'] ?? $name['de'] ?? 'Unknown') : $name;
            
            // Extract set name from JSON if needed
            $setName = $card->set_name;
            if (is_string($setName) && str_starts_with($setName, '{')) {
                $setName = json_decode($setName, true);
            }
            $setNameEn = is_array($setName) ? ($setName['en'] ?? $setName['fr'] ?? $setName['de'] ?? null) : $setName;
            
            return [
                'backend' => 'tcgdex',
                'tcgdex_card_id' => $card->tcgdex_card_id,
                'tcgdex_id' => $card->tcgdex_id,
                'product_id' => null, // Not applicable for TCGDEX
                'name' => $nameEn,
                'card_number' => $card->card_number,
                'set_total' => $card->set_total,
                'set_code' => $card->set_code,
                'set_name' => $setNameEn,
                'image_url' => $card->image_url ? $card->image_url . '/low.webp' : null,
            ];
        });

        return response()->json($formatted);
    }

    /**
     * Search in CMAPI database (Lorcana, One Piece)
     */
    private function searchCmapi(string $query, int $limit, bool $collectionOnly, ?string $gameCode): JsonResponse
    {
        // Escape LIKE wildcards to prevent injection
        $escapedQuery = $this->escapeLikeWildcards($query);
        
        // Build search query
        $results = CmapiCard::query()
            ->select([
                'cmapi_cards.id',
                'cmapi_cards.cmapi_id',
                'cmapi_cards.game',
                'cmapi_cards.name',
                'cmapi_cards.number as card_number',
                'cmapi_cards.set_cmapi_id',
                'cmapi_sets.name as set_name',
                'cmapi_sets.cmapi_episode as set_code',
                'cmapi_sets.card_count as set_total',
                'cmapi_cards.image_small_url as image_url',
            ])
            ->leftJoin('cmapi_sets', 'cmapi_cards.set_cmapi_id', '=', 'cmapi_sets.id');
        
        // Filter by game if set
        if ($gameCode) {
            $results->where('cmapi_cards.game', $gameCode);
        }
        
        // Filter by collection if requested
        if ($collectionOnly && Auth::check()) {
            $userId = Auth::id();
            $results->whereIn('cmapi_cards.cmapi_id', function($query) use ($userId) {
                $query->select('cmapi_card_id')
                    ->from('user_collection')
                    ->where('user_id', $userId)
                    ->whereNotNull('cmapi_card_id'); // Only CMAPI cards
            });
        }
        
        // Search by:
        // - Card name
        // - Card number (like "1", "001")
        // - Set episode (like "1", "2")
        // - Set name
        $results->where(function($q) use ($escapedQuery) {
                $q->where('cmapi_cards.name', 'LIKE', "%{$escapedQuery}%")
                  ->orWhere('cmapi_cards.number', 'LIKE', "%{$escapedQuery}%")
                  ->orWhere('cmapi_sets.cmapi_episode', 'LIKE', "%{$escapedQuery}%")
                  ->orWhere('cmapi_sets.name', 'LIKE', "%{$escapedQuery}%");
            })
            ->orderByRaw(
                'CASE 
                    WHEN cmapi_cards.number = ? THEN 0
                    WHEN cmapi_sets.cmapi_episode = ? THEN 1
                    WHEN cmapi_cards.name LIKE ? THEN 2 
                    WHEN cmapi_cards.number LIKE ? THEN 3
                    WHEN cmapi_sets.name LIKE ? THEN 4
                    ELSE 5 
                END',
                [$escapedQuery, $escapedQuery, "{$escapedQuery}%", "{$escapedQuery}%", "{$escapedQuery}%"]
            )
            ->orderBy('cmapi_sets.release_date', 'DESC')
            ->orderBy('cmapi_cards.number', 'ASC')
            ->orderBy('cmapi_cards.id', 'DESC')
            ->limit($limit);
            
        // Execute query
        $cards = $results->get();

        // Format response (compatible with frontend expectations)
        $formatted = $cards->map(function ($card) {
            return [
                'backend' => 'cmapi',
                'cmapi_card_id' => $card->id,
                'cmapi_id' => $card->cmapi_id,
                'game' => $card->game,
                'product_id' => null, // Not applicable for CMAPI
                'tcgdex_card_id' => null, // Not applicable for CMAPI
                'name' => $card->name,
                'card_number' => $card->card_number,
                'set_total' => $card->set_total,
                'set_code' => $card->set_code,
                'set_name' => $card->set_name,
                'image_url' => $card->image_url,
            ];
        });

        return response()->json($formatted);
    }

    /**
     * Escape LIKE wildcards to prevent injection and unintended matching
     * 
     * @param string $value
     * @return string
     */
    private function escapeLikeWildcards(string $value): string
    {
        // Escape backslash first, then percent and underscore
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
