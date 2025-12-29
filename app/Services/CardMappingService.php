<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CardMappingService
{
    /**
     * Map RapidAPI cards to TCGCSV products
     *
     * @param string $game
     * @return array
     */
    public function mapRapidApiToTcgcsv(string $game = 'pokemon'): array
    {
        $gameId = $this->getGameId($game);
        $stats = [
            'total_rapid' => 0,
            'mapped' => 0,
            'by_method' => [],
        ];

        // Get all RapidAPI cards
        $rapidCards = DB::table('rapidapi_cards')
            ->where('game', $game)
            ->get();

        $stats['total_rapid'] = $rapidCards->count();

        foreach ($rapidCards as $rapid) {
            $mapping = $this->findBestMatch($rapid, $gameId);
            
            if ($mapping) {
                $this->saveMapping($rapid->id, $mapping);
                $stats['mapped']++;
                $stats['by_method'][$mapping['method']] = ($stats['by_method'][$mapping['method']] ?? 0) + 1;
            }
        }

        return $stats;
    }

    /**
     * Find best match for a RapidAPI card
     *
     * @param object $rapid
     * @param int $gameId
     * @return array|null
     */
    protected function findBestMatch(object $rapid, int $gameId): ?array
    {
        // Method 1: Try exact cardmarket_id match
        if ($rapid->cardmarket_id) {
            $cardmarket = DB::table('cardmarket_products')
                ->where('cardmarket_product_id', $rapid->cardmarket_id)
                ->first();
            
            if ($cardmarket && $cardmarket->tcgcsv_product_id) {
                return [
                    'tcgcsv_product_id' => $cardmarket->tcgcsv_product_id,
                    'cardmarket_product_id' => $cardmarket->id,
                    'method' => 'cardmarket_id',
                    'confidence' => 1.00,
                ];
            }
        }

        // Method 2: Try name + card number match
        if ($rapid->name && $rapid->card_number) {
            // Normalize card number (001 vs 1)
            $cardNumber = str_pad($rapid->card_number, 3, '0', STR_PAD_LEFT);
            
            $tcgcsv = DB::table('tcgcsv_products')
                ->where('game_id', $gameId)
                ->where('name', 'LIKE', '%' . $rapid->name . '%')
                ->where(function($query) use ($rapid, $cardNumber) {
                    $query->where('card_number', 'LIKE', $cardNumber . '%')
                          ->orWhere('card_number', 'LIKE', $rapid->card_number . '%');
                })
                ->first();
            
            if ($tcgcsv) {
                return [
                    'tcgcsv_product_id' => $tcgcsv->product_id,
                    'cardmarket_product_id' => $this->findCardmarketByName($rapid->cardmarket_id),
                    'method' => 'name_number',
                    'confidence' => 0.90,
                ];
            }
        }

        // Method 3: Try name + expansion match
        if ($rapid->name && $rapid->episode_name) {
            $tcgcsv = $this->matchByNameAndExpansion($rapid, $gameId);
            
            if ($tcgcsv) {
                return [
                    'tcgcsv_product_id' => $tcgcsv->product_id,
                    'cardmarket_product_id' => $this->findCardmarketByName($rapid->cardmarket_id),
                    'method' => 'name_expansion',
                    'confidence' => 0.75,
                ];
            }
        }

        return null;
    }

    /**
     * Match by name and expansion
     */
    protected function matchByNameAndExpansion(object $rapid, int $gameId): ?object
    {
        // First, find the expansion group
        $group = DB::table('tcgcsv_groups')
            ->where('game_id', $gameId)
            ->where(function($query) use ($rapid) {
                $query->where('name', 'LIKE', '%' . $rapid->episode_name . '%')
                      ->orWhere('abbreviation', $rapid->episode_slug);
            })
            ->first();
        
        if (!$group) {
            return null;
        }

        // Then find the card in that group
        return DB::table('tcgcsv_products')
            ->where('game_id', $gameId)
            ->where('group_id', $group->group_id)
            ->where('name', 'LIKE', '%' . $rapid->name . '%')
            ->first();
    }

    /**
     * Find cardmarket product ID by cardmarket_id
     */
    protected function findCardmarketByName(?int $cardmarketId): ?int
    {
        if (!$cardmarketId) {
            return null;
        }

        $cardmarket = DB::table('cardmarket_products')
            ->where('cardmarket_product_id', $cardmarketId)
            ->first();
        
        return $cardmarket ? $cardmarket->id : null;
    }

    /**
     * Save mapping to database
     */
    protected function saveMapping(int $rapidCardId, array $mapping): void
    {
        $rapidCard = DB::table('rapidapi_cards')->find($rapidCardId);
        
        DB::table('card_mappings')->updateOrInsert(
            ['rapidapi_card_id' => $rapidCardId],
            [
                'rapidapi_card_id' => $rapidCardId,
                'cardmarket_product_id' => $mapping['cardmarket_product_id'],
                'tcgcsv_product_id' => $mapping['tcgcsv_product_id'],
                'game' => $rapidCard->game,
                'match_method' => $mapping['method'],
                'confidence' => $mapping['confidence'],
                'card_name' => $rapidCard->name,
                'card_number' => $rapidCard->card_number,
                'expansion_name' => $rapidCard->episode_name,
                'mapped_at' => now(),
                'updated_at' => now(),
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );
    }

    /**
     * Get game ID for TCGCSV
     */
    protected function getGameId(string $game): int
    {
        return match($game) {
            'pokemon' => 1,
            'mtg' => 2,
            'yugioh' => 3,
            default => 1,
        };
    }

    /**
     * Get mapping statistics
     */
    public function getStatistics(string $game = 'pokemon'): array
    {
        $total = DB::table('card_mappings')->where('game', $game)->count();
        
        $byMethod = DB::table('card_mappings')
            ->where('game', $game)
            ->select('match_method', DB::raw('COUNT(*) as count'))
            ->groupBy('match_method')
            ->get()
            ->pluck('count', 'match_method')
            ->toArray();
        
        $avgConfidence = DB::table('card_mappings')
            ->where('game', $game)
            ->avg('confidence');

        return [
            'total_mappings' => $total,
            'by_method' => $byMethod,
            'avg_confidence' => round($avgConfidence, 2),
        ];
    }
}
