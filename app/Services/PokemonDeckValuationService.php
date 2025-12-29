<?php

namespace App\Services;

use App\Models\Game;
use App\Models\TcgcsvProduct;
use App\Models\TcgcsvPrice;
use App\Models\GuestDeck;
use App\Models\Lead;
use App\Models\DeckValuation;
use App\Models\DeckValuationItem;
use Illuminate\Support\Collection;

class PokemonDeckValuationService
{
    private int $pokemonGameId;

    public function __construct()
    {
        $game = Game::where('name', 'like', '%Pokémon%')->first();
        $this->pokemonGameId = $game ? $game->id : 1;
    }

    /**
     * Search Pokemon cards by name or card number
     */
    public function searchCards(string $query, int $limit = 10): Collection
    {
        $escaped = $this->escapeLikeWildcards($query);

        return TcgcsvProduct::where('game_id', $this->pokemonGameId)
            ->where(function($q) use ($escaped) {
                $q->where('name', 'LIKE', "%{$escaped}%")
                  ->orWhere('card_number', 'LIKE', "%{$escaped}%");
            })
            ->with('group')
            ->orderByRaw("CASE 
                WHEN card_number = ? THEN 0
                WHEN name LIKE ? THEN 1 
                WHEN card_number LIKE ? THEN 2
                ELSE 3 
            END", [$escaped, "{$escaped}%", "{$escaped}%"])
            ->limit($limit)
            ->get()
            ->map(function ($card) {
                return [
                    'product_id' => $card->product_id,
                    'name' => $card->name,
                    'card_number' => $card->card_number,
                    'group_name' => $card->group->name ?? 'Unknown Set',
                    'image_url' => $card->image_url,
                ];
            });
    }

    /**
     * Get or create guest deck from session
     */
    public function getOrCreateGuestDeck(string $sessionUuid = null): GuestDeck
    {
        if ($sessionUuid) {
            $deck = GuestDeck::where('uuid', $sessionUuid)->first();
            if ($deck) {
                return $deck;
            }
        }

        return GuestDeck::create([
            'game_id' => $this->pokemonGameId,
            'status' => 'draft',
            'payload' => [],
            'expires_at' => now()->addDays(30),
        ]);
    }

    /**
     * Add card to session payload
     */
    public function addCardToSession(array &$items, int $productId, int $qty = 1): void
    {
        $key = "card_{$productId}";
        
        if (isset($items[$key])) {
            $items[$key]['qty'] += $qty;
        } else {
            $items[$key] = [
                'product_id' => $productId,
                'qty' => $qty,
            ];
        }
    }

    /**
     * Remove card from session payload
     */
    public function removeCardFromSession(array &$items, int $productId): void
    {
        $key = "card_{$productId}";
        unset($items[$key]);
    }

    /**
     * Sync guest deck payload with session
     */
    public function syncGuestDeckPayload(GuestDeck $deck, array $items): void
    {
        $deck->update(['payload' => $items]);
    }

    /**
     * Create lead and deck valuation
     */
    public function createLeadAndValuation(
        GuestDeck $guestDeck,
        string $email,
        string $deckName,
        bool $consentMarketing,
        array $items
    ): DeckValuation {
        // Create lead
        $lead = Lead::create([
            'email' => $email,
            'deck_name' => $deckName,
            'guest_deck_id' => $guestDeck->id,
            'consent_marketing' => $consentMarketing,
        ]);

        // Update guest deck status and name
        $guestDeck->update([
            'status' => 'lead_captured',
            'name' => $deckName,
        ]);

        // Create deck valuation
        $valuation = DeckValuation::create([
            'lead_id' => $lead->id,
            'guest_deck_id' => $guestDeck->id,
            'name' => $deckName,
        ]);

        // Create valuation items
        foreach ($items as $item) {
            DeckValuationItem::create([
                'deck_valuation_id' => $valuation->id,
                'tcgcsv_product_id' => $item['product_id'],
                'qty' => $item['qty'],
            ]);
        }

        return $valuation;
    }

    /**
     * Compute deck statistics
     */
    public function computeDeckStats(DeckValuation $valuation): array
    {
        $items = $valuation->items()->with([
            'tcgcsvProduct.prices' => function($q) {
                $q->latest('snapshot_at')->limit(1);
            },
            'tcgcsvProduct.rapidapiCard'
        ])->get();

        $totalCards = $items->sum('quantity');
        $uniqueCards = $items->count();
        $totalValueUsd = 0;
        $totalValueEur = 0;
        $cardsWithPricesUsd = 0;
        $cardsWithPricesEur = 0;

        $itemsData = $items->map(function($item) use (&$totalValueUsd, &$totalValueEur, &$cardsWithPricesUsd, &$cardsWithPricesEur) {
            // USD price from TCGPlayer
            $latestPrice = $item->tcgcsvProduct->prices->first();
            $marketPriceUsd = $latestPrice?->market_price ?? 0;
            
            if ($marketPriceUsd > 0) {
                $cardsWithPricesUsd++;
            }

            // EUR price from RapidAPI Cardmarket data
            $marketPriceEur = 0;
            $rapidapiCard = $item->tcgcsvProduct->rapidapiCard;
            if ($rapidapiCard && isset($rapidapiCard->raw_data['prices']['cardmarket']['lowest_near_mint'])) {
                $marketPriceEur = (float) $rapidapiCard->raw_data['prices']['cardmarket']['lowest_near_mint'];
                if ($marketPriceEur > 0) {
                    $cardsWithPricesEur++;
                }
            }

            $lineTotalUsd = $marketPriceUsd * $item->quantity;
            $lineTotalEur = $marketPriceEur * $item->quantity;
            $totalValueUsd += $lineTotalUsd;
            $totalValueEur += $lineTotalEur;

            return [
                'card' => $item->tcgcsvProduct,
                'quantity' => $item->quantity,
                'market_price_usd' => $marketPriceUsd,
                'market_price_eur' => $marketPriceEur,
                'line_total_usd' => $lineTotalUsd,
                'line_total_eur' => $lineTotalEur,
            ];
        });

        // Sort by EUR line_total descending and get top 10
        $top10 = $itemsData->sortByDesc('line_total_eur')->take(10)->values();

        return [
            'total_cards' => $totalCards,
            'unique_cards' => $uniqueCards,
            'total_value_usd' => round($totalValueUsd, 2),
            'total_value_eur' => round($totalValueEur, 2),
            'cards_with_prices_usd' => $cardsWithPricesUsd,
            'cards_with_prices_eur' => $cardsWithPricesEur,
            'top_10_cards' => $top10,
            'all_items' => $itemsData,
        ];
    }

    /**
     * Attach valuation to authenticated user
     */
    public function attachToUser(DeckValuation $valuation, int $userId): void
    {
        $valuation->update(['user_id' => $userId]);
        
        if ($valuation->guestDeck) {
            $valuation->guestDeck->update(['status' => 'attached']);
        }
    }

    private function escapeLikeWildcards(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
