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
 * Note: RapidAPI uses "one-piece" in the URL path, while our internal game
 * code remains "onepiece". The client handles this mapping transparently.
 * 
 * - GET /{game}/episodes           - List all sets ("episodes")
 * - GET /{game}/episodes/{id}/cards - List cards in a set
 * - GET /{game}/cards/{id}         - Get single card
 * - GET /{game}/cards?search={query} - Search cards
 */
class CmapiClient
{
    protected const DEFAULT_PAGE_SIZE = 100;
    protected const MAX_PAGES = 200;

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
        return $this->fetchPaginatedCollection("/{$this->getApiGameSlug()}/episodes", 'listSets');
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
        return $this->fetchPaginatedCollection(
            "/{$this->getApiGameSlug()}/episodes/{$episodeId}/cards",
            'listCardsBySet',
            ['episode_id' => $episodeId]
        );
    }

    /**
     * Fetch single card details
     */
    public function getCard(string $cardId): ?array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders($this->getHeaders())
            ->get("{$this->baseUrl}/{$this->getApiGameSlug()}/cards/{$cardId}");

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
            // Prefer explicit printed total, then total_cards, then generic card_count
            'card_count' => $episodeData['cards_printed_total']
                ?? $episodeData['total_cards']
                ?? $episodeData['card_count']
                ?? null,
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
            'tcggo_url' => $cardData['links']['cardmarket'] ?? $cardData['tcggo_url'] ?? null,
            'cardmarket_id' => $cardData['cardmarket_id'] ?? null,
            'hp' => $cardData['hp'] ?? null,
            'raw' => $cardData,
            'prices' => $cardData['prices'] ?? null,
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
            if (is_numeric($price) && $price !== null) {
                return round((float)$price, 2);
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

    /**
     * Map internal game code to the API path segment.
     *
     * RapidAPI expects "one-piece" in the URL, while we use "onepiece"
     * internally for consistency across the app.
     */
    protected function getApiGameSlug(): string
    {
        if ($this->game === 'onepiece') {
            return 'one-piece';
        }

        return $this->game;
    }

    /**
     * Fetch paginated collections from CMAPI endpoints.
     *
     * Handles both paginated and non-paginated responses.
     * Supports common pagination shapes (meta, pagination, links, has_more).
     */
    protected function fetchPaginatedCollection(string $endpoint, string $context, array $logContext = []): array
    {
        $pageSize = (int) config('cmapi.page_size', self::DEFAULT_PAGE_SIZE);
        $pageSize = $pageSize > 0 ? $pageSize : self::DEFAULT_PAGE_SIZE;

        $allItems = [];
        $page = 1;
        $lastPageSignature = null;

        while ($page <= self::MAX_PAGES) {
            $queryParams = [];
            if ($page > 1) {
                $queryParams['page'] = $page;
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getHeaders())
                ->get("{$this->baseUrl}{$endpoint}", $queryParams);

            if (!$response->successful()) {
                Log::error("CMAPI {$context} failed: {$response->status()}", array_merge($logContext, [
                    'game' => $this->game,
                    'page' => $page,
                    'body' => $response->body(),
                ]));
                throw new \Exception("Failed to fetch {$context}: {$response->status()}");
            }

            $payload = $response->json();
            $items = $this->extractCollectionItems($payload);

            if (empty($items)) {
                break;
            }

            // Guard: if API ignores page param and keeps returning same payload, stop.
            $signature = md5(json_encode($items));
            if ($lastPageSignature !== null && $signature === $lastPageSignature) {
                Log::warning("CMAPI {$context} appears non-advancing, stopping pagination loop", array_merge($logContext, [
                    'game' => $this->game,
                    'page' => $page,
                ]));
                break;
            }
            $lastPageSignature = $signature;

            $allItems = array_merge($allItems, $items);

            if (!$this->hasMorePages($payload, $items, $page, $pageSize)) {
                break;
            }

            $page++;
        }

        if ($page > self::MAX_PAGES) {
            Log::warning("CMAPI {$context} reached max pages safety limit", array_merge($logContext, [
                'game' => $this->game,
                'max_pages' => self::MAX_PAGES,
            ]));
        }

        return $allItems;
    }

    /**
     * Extract collection items from different response shapes.
     */
    protected function extractCollectionItems($payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        // Common API shape: { data: [...] }
        if (isset($payload['data']) && is_array($payload['data'])) {
            return array_values(array_filter($payload['data'], 'is_array'));
        }

        // Some APIs return raw list directly.
        if (array_is_list($payload)) {
            return array_values(array_filter($payload, 'is_array'));
        }

        return [];
    }

    /**
     * Determine whether another page likely exists.
     */
    protected function hasMorePages(array $payload, array $items, int $page, int $pageSize): bool
    {
        // RapidAPI shape used in existing commands: { paging: { current, total } }
        if (isset($payload['paging']) && is_array($payload['paging'])) {
            $current = (int) ($payload['paging']['current'] ?? $page);
            $total = (int) ($payload['paging']['total'] ?? $current);
            if ($total > 0) {
                return $current < $total;
            }
        }

        // Laravel-style metadata: { meta: { current_page, last_page } }
        if (isset($payload['meta']) && is_array($payload['meta'])) {
            $current = (int) ($payload['meta']['current_page'] ?? $page);
            $last = (int) ($payload['meta']['last_page'] ?? $current);
            if ($last > 0) {
                return $current < $last;
            }
        }

        // Alternate metadata: { pagination: { current_page, last_page } }
        if (isset($payload['pagination']) && is_array($payload['pagination'])) {
            $current = (int) ($payload['pagination']['current_page'] ?? $page);
            $last = (int) ($payload['pagination']['last_page'] ?? $current);
            if ($last > 0) {
                return $current < $last;
            }
        }

        // Link-based pagination.
        if (isset($payload['next_page_url']) && !empty($payload['next_page_url'])) {
            return true;
        }
        if (isset($payload['links']) && is_array($payload['links']) && !empty($payload['links']['next'])) {
            return true;
        }

        // Boolean indicator.
        if (isset($payload['has_more'])) {
            return (bool) $payload['has_more'];
        }

        // Total/per-page indicator.
        if (isset($payload['total'])) {
            $total = (int) $payload['total'];
            return ($page * $pageSize) < $total;
        }
        if (isset($payload['meta']['total'])) {
            $total = (int) $payload['meta']['total'];
            return ($page * $pageSize) < $total;
        }

        // Fallback: if full page returned, assume there may be another page.
        return count($items) >= $pageSize;
    }
}
