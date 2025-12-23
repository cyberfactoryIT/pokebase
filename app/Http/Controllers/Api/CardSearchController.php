<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CardSearchRequest;
use App\Models\TcgcsvProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

            // Escape LIKE wildcards to prevent injection
            $escapedQuery = $this->escapeLikeWildcards($query);
            
            // Build search query with prefix/contains ranking
            $results = TcgcsvProduct::query()
                ->select([
                    'tcgcsv_products.product_id',
                    'tcgcsv_products.name',
                    'tcgcsv_products.card_number',
                    'tcgcsv_products.group_id',
                    'tcgcsv_groups.name as group_name',
                    'tcgcsv_groups.published_on as group_published_on',
                    'tcgcsv_products.image_url',
                ])
                ->leftJoin('tcgcsv_groups', 'tcgcsv_products.group_id', '=', 'tcgcsv_groups.group_id')
                ->where('tcgcsv_products.name', 'LIKE', "%{$escapedQuery}%")
                ->orderByRaw(
                    'CASE WHEN tcgcsv_products.name LIKE ? THEN 0 ELSE 1 END',
                    ["{$escapedQuery}%"]
                )
                ->orderByRaw('tcgcsv_groups.published_on IS NULL')
                ->orderBy('tcgcsv_groups.published_on', 'DESC')
                ->orderBy('tcgcsv_products.card_number', 'ASC')
                ->orderBy('tcgcsv_products.id', 'ASC')
                ->limit($limit)
                ->get();

            // Format response
            $formatted = $results->map(function ($card) {
                return [
                    'product_id' => $card->product_id,
                    'name' => $card->name,
                    'card_number' => $card->card_number,
                    'group_id' => $card->group_id,
                    'group_name' => $card->group_name,
                    'group_published_on' => $card->group_published_on 
                        ? (new \DateTime($card->group_published_on))->format('Y-m-d')
                        : null,
                    'image_url' => $card->image_url,
                ];
            });

            return response()->json($formatted);

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
