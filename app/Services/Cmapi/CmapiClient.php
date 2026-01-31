<?php

namespace App\Services\Cmapi;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CardMarket API Client (via RapidAPI)
 * 
 * Documentation: https://rapidapi.com/tcggopro/api/cardmarket-api-tcg
 * Supported games: lorcana, onepiece
 * 
 * Endpoints:
 * - GET /{game}/episodes - List all sets ("episodes")
 * - GET /{game}/episodes/{id}/cards - List cards in a set
 * - GET /{game}/cards/{id} - Get single card
 * - GET /{game}/cards?search={query} - Search cards
 */
class CmapiClient
{
    protected string $baseUrl;
    protected int $timeout;
    protected string $rapidApiKey;
    protected string $rapidApiHost;
    protected string $game;

    public function __construct(string $game = 'lorcana')
    {
        $this->baseUrl = config('cmapi.base_url');
        $this->timeout = config('cmapi.timeout', 30);
        $this->rapidApiKey = config('cmapi.rapidapi_key');
        $this->rapidApiHost = config('cmapi.rapidapi_host');
        $this->game = $game;
    }

    /**
     * Fetch all sets (called "episodes" in CMAPI)
     */
    public function listSets(): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders($this->getHeaders())
            ->get("{$this->baseUrl}/{$this->game}/episodes");

        if (!$response->successful()) {
            Log::error("CMAPI listSets failed: {$response->status()}", [
                'game' => $this->game,
                'body' => $response->body(),
            ]);
            throw new \Exception("Failed to fetch sets: {$response->status()}");
        }

        $data = $response->json();
        return $data['data'] ?? [];
    }

    /**
     * Fetch single set details
     * Note: CMAPI doesn't have dedicated episode detail endpoint
     * We get all episodes and filter by ID
     */
    public function getSet(string $episodeId): ?array
    {
        $episodes = $this->listSets();
        
        foreach ($episodes as $episode) {
            if (($episode['id'] ?? null) == $episodeId) {
                return $episode;
            }
        }
        
        return null;
    }

    /**
     * Fetch cards for a set/episode
     */
    public function listCardsBySet(string $episodeId): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders($this->getHeaders())
            ->get("{$this->baseUrl}/{$this->game}/episodes/{$episodeId}/cards");

        if (!$response->successful()) {
            Log::error("CMAPI listCardsBySet failed: {$response->status()}", [
                'game' => $this->game,
                'episode_id' => $episodeId,
                'body' => $response->body(),
            ]);
            throw new \Exception("Failed to fetch cards for episode {$episodeId}: {$response->status()}");
        }

        $data = $response->json();
        return $data['data'] ?? [];
    }

    /**
     * Fetch single card details
     */
    public function getCard(string $cardId): ?array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders($this->getHeaders())
            ->get("{$this->baseUrl}/{$this->game}/cards/{$cardId}");

        if (!$response->successful()) {
            Log::warning("CMAPI getCard failed: {$response->status()}", [
                'game' => $this->game,
                'card_id' => $cardId,
            ]);
            return null;
        }

        return $response->json();
    }

    /**
     * Normalize set/episode data for database
     */
    public function normalizeSet(array $episodeData): array
    {
        return [
            'cmapi_id' => (string)$episodeData['id'],
            'game' => $this->game,
            'cmapi_episode' => $episodeData['id'],
            'name' => $episodeData['name'] ?? $episodeData['title'] ?? 'Unknown',
            'code' => $episodeData['code'] ?? $episodeData['slug'] ?? null,
            'logo_url' => $episodeData['logo'] ?? $episodeData['logo_url'] ?? null,
            'release_date' => $episodeData['release_date'] ?? $episodeData['released_at'] ?? null,
            'card_count' => $episodeData['card_count'] ?? $episodeData['total_cards'] ?? null,
            'raw' => $episodeData,
        ];
    }

    /**
     * Normalize card data for database
     */
    public function normalizeCard(array $cardData, int $setDbId): array
    {
        if (!$setDbId) {
            throw new \Exception("Invalid set_id: cannot be null or 0");
        }

        // Extract pricing from nested structure
        $priceEur = $this->extractPrice($cardData, 'cardmarket', 'lowest_near_mint');
        $priceUsd = null; // CardMarket API doesn't provide USD prices

        // Base card data
        $normalized = [
            'cmapi_id' => (string)$cardData['id'],
            'game' => $this->game,
            'set_cmapi_id' => $setDbId,
            'name' => $cardData['name'],
            'number' => $cardData['number'] ?? $cardData['card_number'] ?? null,
            'rarity' => $cardData['rarity'] ?? null,
            'image_small_url' => $cardData['image'] ?? $cardData['image_url'] ?? null,
            'image_large_url' => $cardData['image'] ?? $cardData['image_url_hires'] ?? null,
            'price_eur' => $priceEur,
            'price_usd' => $priceUsd,
            'artist_name' => $cardData['artist']['name'] ?? null,
            'slug' => $cardData['slug'] ?? null,
            'tcggo_url' => $cardData['tcggo_url'] ?? null,
            'cardmarket_id' => $cardData['cardmarket_id'] ?? null,
            'hp' => $cardData['hp'] ?? null,
            'raw' => $cardData,
        ];

        // Add Lorcana-specific fields if present
        if (isset($cardData['ink_cost'])) {
            $normalized['ink_cost'] = $cardData['ink_cost'];
        }
        if (isset($cardData['card_type']) || isset($cardData['type'])) {
            $normalized['card_type'] = $cardData['card_type'] ?? $cardData['type'];
        }
        if (isset($cardData['lore'])) {
            $normalized['lore_value'] = $cardData['lore'];
        }
        if (isset($cardData['ink_color']) || isset($cardData['color'])) {
            $normalized['ink_color'] = $cardData['ink_color'] ?? $cardData['color'];
        }

        // Add One Piece-specific fields if present
        if (isset($cardData['cost'])) {
            $normalized['cost'] = $cardData['cost'];
        }
        if (isset($cardData['power'])) {
            $normalized['power'] = $cardData['power'];
        }
        if (isset($cardData['counter'])) {
            $normalized['counter'] = $cardData['counter'];
        }

        return $normalized;
    }

    /**
     * Extract price from CMAPI nested pricing structure
     * 
     * Example structure:
     * {
     *   "prices": {
     *     "cardmarket": {
     *       "currency": "EUR",
     *       "lowest_near_mint": 0.03,
     *       "lowest_near_mint_DE": 0.02,
     *       "30d_average": 0.19
     *     }
     *   }
     * }
     */
    protected function extractPrice(array $cardData, string $marketplace, string $priceKey): ?float
    {
        // Check nested prices structure
        if (isset($cardData['prices'][$marketplace][$priceKey])) {
            $price = $cardData['prices'][$marketplace][$priceKey];
            // Prices are already in decimal format (EUR)
            if ($price && is_numeric($price)) {
                return round($price, 2);
            }
        }

        return null;
    }

    /**
     * Get RapidAPI headers
     */
    protected function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'X-RapidAPI-Key' => $this->rapidApiKey,
            'X-RapidAPI-Host' => $this->rapidApiHost,
        ];
    }
}
