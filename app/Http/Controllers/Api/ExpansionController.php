<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\TcgcsvGroup;
use App\Models\TcgcsvProduct;
use App\Models\UserCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Expansion API Controller
 * 
 * Provides endpoints for expansion-related data and completion tracking.
 */
class ExpansionController extends Controller
{
    /**
     * Get missing cards for a specific expansion
     * 
     * GET /api/expansions/{id}/missing-cards
     * 
     * Returns:
     * - missing_cards: array of cards not in user's collection
     * - owned_count: number of cards owned
     * - total_count: total cards in expansion
     * - completion_percentage: float (0-100)
     * 
     * @param int $expansionId
     * @return JsonResponse
     */
    public function getMissingCards(int $expansionId): JsonResponse
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                ], 401);
            }

            $userId = Auth::id();

            // Verify expansion exists (using group_id, not primary key)
            $expansion = TcgcsvGroup::where('group_id', $expansionId)->first();
            if (!$expansion) {
                return response()->json([
                    'error' => 'Expansion not found',
                ], 404);
            }

            // Get all cards in this expansion (only with card_number)
            $allCards = TcgcsvProduct::where('group_id', $expansionId)
                ->whereNotNull('card_number')
                ->where('card_number', '!=', '')
                ->orderBy('card_number', 'ASC')
                ->get();

            $totalCount = $allCards->count();

            // Get cards user already owns from this expansion
            $ownedCardIds = UserCollection::where('user_id', $userId)
                ->whereIn('product_id', $allCards->pluck('product_id'))
                ->pluck('product_id')
                ->toArray();

            $ownedCount = count($ownedCardIds);

            // Filter to get missing cards
            $missingCards = $allCards->filter(function ($card) use ($ownedCardIds) {
                return !in_array($card->product_id, $ownedCardIds);
            })->map(function ($card) {
                return [
                    'id' => $card->product_id,
                    'name' => $card->name,
                    'number' => $card->card_number,
                    'rarity' => $card->rarity,
                    'image_url' => $card->image_url,
                ];
            })->values()->take(50); // Limit to 50 cards for performance

            // Calculate completion percentage
            $completionPercentage = $totalCount > 0 
                ? round(($ownedCount / $totalCount) * 100, 2) 
                : 0;

            return response()->json([
                'expansion_id' => $expansionId,
                'expansion_name' => $expansion->name,
                'missing_cards' => $missingCards,
                'owned_count' => $ownedCount,
                'total_count' => $totalCount,
                'completion_percentage' => $completionPercentage,
            ]);

        } catch (\Exception $e) {
            Log::error('Get missing cards API error', [
                'expansion_id' => $expansionId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'An unexpected error occurred while fetching missing cards',
            ], 500);
        }
    }
    
    /**
     * Get user's popular sets (most complete sets)
     */
    public function getPopularSets(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $currentGame = Game::where('code', session('current_game', 'pokemon'))->first();
            
            if (!$currentGame) {
                Log::warning('Popular sets: No current game found');
                return response()->json(['sets' => []]);
            }
            
            Log::info('Popular sets: Loading for user ' . $userId . ' game ' . $currentGame->code);
            
            // Get user's sets with card counts (only cards with card_number)
            $userSets = DB::table('user_collection')
                ->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.product_id')
                ->join('tcgcsv_groups', 'tcgcsv_products.group_id', '=', 'tcgcsv_groups.group_id')
                ->where('user_collection.user_id', $userId)
                ->where('tcgcsv_groups.game_id', $currentGame->id)
                ->whereNotNull('tcgcsv_products.card_number')
                ->where('tcgcsv_products.card_number', '!=', '')
                ->select(
                    'tcgcsv_groups.group_id',
                    'tcgcsv_groups.name',
                    DB::raw('COUNT(DISTINCT tcgcsv_products.product_id) as owned_count')
                )
                ->groupBy('tcgcsv_groups.group_id', 'tcgcsv_groups.name')
                ->get();
            
            Log::info('Popular sets: Found ' . $userSets->count() . ' sets');
            
            $sets = [];
        
        foreach ($userSets as $userSet) {
            // Get total cards in set (only with card_number)
            $totalCards = DB::table('tcgcsv_products')
                ->where('group_id', $userSet->group_id)
                ->where('game_id', $currentGame->id)
                ->whereNotNull('card_number')
                ->where('card_number', '!=', '')
                ->distinct()
                ->count('product_id');
            
            if ($totalCards > 0) {
                $completion = round(($userSet->owned_count / $totalCards) * 100, 1);
                
                $sets[] = [
                    'group_id' => $userSet->group_id,
                    'name' => $userSet->name,
                    'owned' => (int)$userSet->owned_count,
                    'total' => $totalCards,
                    'completion' => $completion,
                ];
            }
        }
        
        // Sort by completion (most complete first, then by owned count)
        usort($sets, function($a, $b) {
            if ($b['completion'] == $a['completion']) {
                return $b['owned'] - $a['owned'];
            }
            return $b['completion'] <=> $a['completion'];
        });
        
        // Limit to top 4 sets for dashboard
        $sets = array_slice($sets, 0, 4);
        
        Log::info('Popular sets: Returning ' . count($sets) . ' sets');
        
        return response()->json(['sets' => $sets]);
        
        } catch (\Exception $e) {
            Log::error('Error loading popular sets: ' . $e->getMessage());
            return response()->json(['sets' => [], 'error' => $e->getMessage()], 500);
        }
    }
}
