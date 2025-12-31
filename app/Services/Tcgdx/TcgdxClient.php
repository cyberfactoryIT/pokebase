<?php

namespace App\Services\Tcgdx;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

/**
 * TCGdex API Client
 * 
 * Endpoints used:
 * - GET /en/sets - List all sets
 * - GET /en/sets/{setId} - Get set details with cards
 * - GET /en/cards/{cardId} - Get single card (if needed)
 * 
 * Documentation: https://tcgdex.dev/docs
 */
class TcgdxClient
{
    protected string $baseUrl;
    protected int $timeout;
    protected int $retryCount;
    protected int $retrySleepMs;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('tcgdx.base_url', 'https://api.tcgdex.net/v2');
        $this->timeout = config('tcgdx.timeout', 30);
        $this->retryCount = config('tcgdx.retry_count', 3);
        $this->retrySleepMs = config('tcgdx.retry_sleep_ms', 1000);
        $this->apiKey = config('tcgdx.api_key');
    }

    /**
     * List all Pokemon sets
     * 
     * @return array Array of set summaries
     * @throws RequestException
     */
    public function listSets(): array
    {
        $response = $this->makeRequest('GET', '/en/sets');
        
        return $response ?? [];
    }

    /**
     * Get full set details including cards list
     * 
     * @param string $setId Set identifier (e.g., "base1", "sv01")
     * @return array|null Full set payload with cards
     * @throws RequestException
     */
    public function getSet(string $setId): ?array
    {
        $response = $this->makeRequest('GET', "/en/sets/{$setId}");
        
        return $response;
    }

    /**
     * List cards for a specific set
     * 
     * @param string $setId Set identifier
     * @return array Array of cards
     * @throws RequestException
     */
    public function listCardsBySet(string $setId): array
    {
        $set = $this->getSet($setId);
        
        // TCGdex returns cards in the set detail endpoint
        return $set['cards'] ?? [];
    }

    /**
     * Get single card details
     * 
     * @param string $cardId Card identifier (e.g., "base1-1")
     * @return array|null Card payload
     * @throws RequestException
     */
    public function getCard(string $cardId): ?array
    {
        $response = $this->makeRequest('GET', "/en/cards/{$cardId}");
        
        return $response;
    }

    /**
     * Make HTTP request with retry logic
     * 
     * @param string $method
     * @param string $endpoint
     * @return array|null
     * @throws RequestException
     */
    protected function makeRequest(string $method, string $endpoint): ?array
    {
        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'User-Agent' => 'PokeBase/' . config('app.version', '1.0') . ' (Laravel)',
            'Accept' => 'application/json',
        ];
        
        // Add API key if configured (for future premium features)
        if ($this->apiKey) {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }
        
        $response = Http::withHeaders($headers)
            ->timeout($this->timeout)
            ->retry($this->retryCount, $this->retrySleepMs, function ($exception, $request) {
                // Retry on 5xx errors or rate limit (429)
                if ($exception instanceof RequestException) {
                    return in_array($exception->response->status(), [429, 500, 502, 503, 504]);
                }
                return false;
            })
            ->$method($url);

        if ($response->successful()) {
            // Small delay to respect rate limits
            usleep(100000); // 100ms delay between requests
            return $response->json();
        }

        if ($response->status() === 404) {
            return null;
        }
        
        if ($response->status() === 429) {
            // Rate limited - wait longer
            sleep(5);
            throw new RequestException($response);
        }

        $response->throw();
        
        return null;
    }

    /**
     * Normalize set data from API response
     * 
     * @param array $setData
     * @return array
     */
    public function normalizeSet(array $setData): array
    {
        return [
            'tcgdex_id' => $setData['id'] ?? null,
            'name' => $this->extractName($setData),
            'series' => $setData['serie']['name'] ?? $setData['series']['name'] ?? null,
            'logo_url' => $setData['logo'] ?? null,
            'symbol_url' => $setData['symbol'] ?? null,
            'release_date' => $setData['releaseDate'] ?? null,
            'card_count_total' => $setData['cardCount']['total'] ?? null,
            'card_count_official' => $setData['cardCount']['official'] ?? null,
            'raw' => $setData,
        ];
    }

    /**
     * Normalize card data from API response
     * 
     * @param array $cardData
     * @param int $setDbId
     * @return array
     */
    public function normalizeCard(array $cardData, int $setDbId): array
    {
        return [
            'tcgdex_id' => $cardData['id'] ?? null,
            'set_tcgdx_id' => $setDbId,
            'local_id' => $cardData['localId'] ?? null,
            'number' => $cardData['number'] ?? $cardData['localId'] ?? null,
            'name' => $this->extractName($cardData),
            'rarity' => $cardData['rarity'] ?? null,
            'illustrator' => $cardData['illustrator'] ?? null,
            'image_small_url' => $cardData['image'] ?? null,
            'image_large_url' => $cardData['image'] ?? null,
            'types' => $cardData['types'] ?? null,
            'subtypes' => isset($cardData['category']) ? [$cardData['category']] : null,
            'supertype' => $cardData['category'] ?? null,
            'hp' => isset($cardData['hp']) ? (int) $cardData['hp'] : null,
            'evolves_from' => $cardData['evolveFrom'] ?? null,
            'raw' => $cardData,
        ];
    }

    /**
     * Extract name field (handle multilingual or simple string)
     * 
     * @param array $data
     * @return array
     */
    protected function extractName(array $data): array
    {
        $name = $data['name'] ?? [];
        
        if (is_string($name)) {
            return ['en' => $name];
        }
        
        if (is_array($name) && !empty($name)) {
            return $name;
        }
        
        return ['en' => 'Unknown'];
    }
}
