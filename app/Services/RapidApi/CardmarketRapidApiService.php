<?php

namespace App\Services\RapidApi;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CardmarketRapidApiService
{
    protected string $apiKey;
    protected string $host;
    protected string $baseUrl;
    protected int $delayMs;

    public function __construct()
    {
        $this->apiKey = config('rapidapi.cardmarket.api_key');
        $this->host = config('rapidapi.cardmarket.host');
        $this->baseUrl = config('rapidapi.cardmarket.base_url');
        $this->delayMs = config('rapidapi.cardmarket.rate_limit.delay_ms', 1200);
    }

    /**
     * Fetch Pokemon cards with pagination
     *
     * @param int $page
     * @param string|null $sort
     * @return array
     */
    public function fetchPokemonCards(int $page = 1, ?string $sort = 'episode_newest'): array
    {
        $endpoint = config('rapidapi.cardmarket.endpoints.pokemon_cards');
        
        $params = [
            'sort' => $sort,
        ];

        if ($page > 1) {
            $params['page'] = $page;
        }

        return $this->makeRequest($endpoint, $params);
    }

    /**
     * Fetch all episodes/expansions
     *
     * @param string $game pokemon|mtg|yugioh
     * @return array
     */
    public function fetchEpisodes(string $game = 'pokemon'): array
    {
        $endpoint = "/{$game}/episodes";
        return $this->makeRequest($endpoint);
    }

    /**
     * Fetch cards from a specific episode
     *
     * @param string $game
     * @param int $episodeId
     * @param string $sort
     * @param int $page
     * @return array
     */
    public function fetchEpisodeCards(string $game, int $episodeId, string $sort = 'price_highest', int $page = 1): array
    {
        $endpoint = "/{$game}/episodes/{$episodeId}/cards";
        
        $params = ['sort' => $sort];
        if ($page > 1) {
            $params['page'] = $page;
        }
        
        return $this->makeRequest($endpoint, $params);
    }

    /**
     * Fetch MTG cards with pagination
     *
     * @param int $page
     * @param string|null $sort
     * @return array
     */
    public function fetchMtgCards(int $page = 1, ?string $sort = 'episode_newest'): array
    {
        $endpoint = config('rapidapi.cardmarket.endpoints.mtg_cards');
        
        $params = [
            'sort' => $sort,
        ];

        if ($page > 1) {
            $params['page'] = $page;
        }

        return $this->makeRequest($endpoint, $params);
    }

    /**
     * Fetch Yu-Gi-Oh! cards with pagination
     *
     * @param int $page
     * @param string|null $sort
     * @return array
     */
    public function fetchYugiohCards(int $page = 1, ?string $sort = 'episode_newest'): array
    {
        $endpoint = config('rapidapi.cardmarket.endpoints.yugioh_cards');
        
        $params = [
            'sort' => $sort,
        ];

        if ($page > 1) {
            $params['page'] = $page;
        }

        return $this->makeRequest($endpoint, $params);
    }

    /**
     * Fetch all pages for a game
     *
     * @param string $game pokemon|mtg|yugioh
     * @param int|null $maxPages Limit number of pages to fetch (null = all)
     * @param int $startPage Start from specific page (default 1)
     * @return array
     */
    public function fetchAllPages(string $game, ?int $maxPages = null, int $startPage = 1): array
    {
        $allCards = [];
        $page = $startPage;
        $totalPages = null;

        do {
            Log::info("Fetching {$game} cards", ['page' => $page]);

            $response = match($game) {
                'pokemon' => $this->fetchPokemonCards($page),
                'mtg' => $this->fetchMtgCards($page),
                'yugioh' => $this->fetchYugiohCards($page),
                default => throw new \InvalidArgumentException("Invalid game: {$game}"),
            };

            if (!isset($response['data'])) {
                break;
            }

            $allCards = array_merge($allCards, $response['data']);

            // Get total pages from first response
            if ($totalPages === null && isset($response['paging']['total'])) {
                $totalPages = $response['paging']['total'];
                Log::info("Total pages for {$game}: {$totalPages}");
            }

            $page++;

            // Check if we should stop
            if ($maxPages && $page > ($startPage + $maxPages - 1)) {
                break;
            }

            // Check if we reached the end
            if ($totalPages && $page > $totalPages) {
                break;
            }

            // Rate limiting delay
            usleep($this->delayMs * 1000);

        } while (true);

        return [
            'cards' => $allCards,
            'total' => count($allCards),
            'pages_fetched' => $page - $startPage,
            'total_pages' => $totalPages,
        ];
    }

    /**
     * Make HTTP request to RapidAPI
     *
     * @param string $endpoint
     * @param array $params
     * @return array
     */
    protected function makeRequest(string $endpoint, array $params = []): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-rapidapi-host' => $this->host,
                    'x-rapidapi-key' => $this->apiKey,
                ])
                ->get($this->baseUrl . $endpoint, $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('RapidAPI request failed', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];

        } catch (\Exception $e) {
            Log::error('RapidAPI request exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get card statistics
     *
     * @param array $cards
     * @return array
     */
    public function getStatistics(array $cards): array
    {
        $stats = [
            'total_cards' => count($cards),
            'by_rarity' => [],
            'by_supertype' => [],
            'price_ranges' => [
                'lowest' => null,
                'highest' => null,
                'average' => 0,
            ],
        ];

        $totalPrice = 0;
        $priceCount = 0;

        foreach ($cards as $card) {
            // Count by rarity
            $rarity = $card['rarity'] ?? 'Unknown';
            $stats['by_rarity'][$rarity] = ($stats['by_rarity'][$rarity] ?? 0) + 1;

            // Count by supertype
            $supertype = $card['supertype'] ?? 'Unknown';
            $stats['by_supertype'][$supertype] = ($stats['by_supertype'][$supertype] ?? 0) + 1;

            // Price statistics
            if (isset($card['prices']['cardmarket']['lowest_near_mint'])) {
                $price = $card['prices']['cardmarket']['lowest_near_mint'];
                
                if ($stats['price_ranges']['lowest'] === null || $price < $stats['price_ranges']['lowest']) {
                    $stats['price_ranges']['lowest'] = $price;
                }
                
                if ($stats['price_ranges']['highest'] === null || $price > $stats['price_ranges']['highest']) {
                    $stats['price_ranges']['highest'] = $price;
                }
                
                $totalPrice += $price;
                $priceCount++;
            }
        }

        if ($priceCount > 0) {
            $stats['price_ranges']['average'] = round($totalPrice / $priceCount, 2);
        }

        return $stats;
    }
}
