<?php

namespace App\Console\Commands;

use App\Models\PipelineRun;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RapidapiSyncCards extends Command
{
    protected $signature = 'rapidapi:sync-cards
                          {game=pokemon : The game to sync (pokemon, yugioh, magic)}
                          {--episode= : Specific episode ID to sync}
                          {--limit= : Limit number of episodes to process}';

    protected $description = 'Sync cards from most recent RapidAPI episodes with price history';

    private $apiKey;
    private $baseUrl;

    public function handle()
    {
        $game = $this->argument('game');
        $specificEpisode = $this->option('episode');
        $limit = $this->option('limit');

        // Start pipeline tracking
        $pipelineRun = PipelineRun::start('rapidapi:sync-cards', ['game' => $game]);

        $this->apiKey = config('rapidapi.cardmarket.api_key');
        $this->baseUrl = config('rapidapi.cardmarket.base_url');

        if (!$this->apiKey) {
            $this->error('RapidAPI key not configured!');
            $pipelineRun->markFailed('RapidAPI key not configured');
            return 1;
        }

        $this->info("Starting card sync for {$game}...");

        // Get episodes to process (most recent first, only those needing update)
        $query = DB::table('rapidapi_episodes')->where('game', $game);

        if ($specificEpisode) {
            $query->where('episode_id', $specificEpisode);
        } else {
            // Sync all episodes that need daily update
            // Only episodes with cards and not updated today
            $query->where('cards_total', '>', 0)
                  ->where(function($q) {
                      $q->whereNull('cards_updated_at')
                        ->orWhereDate('cards_updated_at', '<', now()->toDateString());
                  });
        }

        $episodes = $query->orderBy('released_at', 'desc')
                          ->when($limit, fn($q) => $q->limit($limit))
                          ->get();

        if ($episodes->isEmpty()) {
            $this->info('No episodes need updating.');
            return 0;
        }

        $this->info("Found {$episodes->count()} episodes to process.");

        $totalCards = 0;
        $totalPrices = 0;
        $errors = 0;
        $processedCount = 0;

        foreach ($episodes as $episode) {
            $processedCount++;
            $this->info("Processing [{$processedCount}/{$episodes->count()}]: {$episode->name} (ID: {$episode->episode_id})");

            try {
                $result = $this->syncEpisodeCards($episode);
                
                $totalCards += $result['cards'];
                $totalPrices += $result['prices'];

                // Mark episode as updated
                DB::table('rapidapi_episodes')
                    ->where('episode_id', $episode->episode_id)
                    ->update(['cards_updated_at' => now()]);

                $this->info("  ✓ {$result['cards']} cards, {$result['prices']} prices saved");
                
                // Update pipeline stats every 10 episodes
                if ($processedCount % 10 === 0) {
                    $pipelineRun->updateStats([
                        'rows_processed' => $totalCards,
                        'rows_created' => $totalPrices,
                        'errors_count' => $errors,
                    ]);
                }

            } catch (\Exception $e) {
                $errors++;
                
                // Check if it's a MySQL connection error - try to reconnect
                if (str_contains($e->getMessage(), 'MySQL server has gone away') || 
                    str_contains($e->getMessage(), 'No such file or directory')) {
                    $this->warn("  ⚠️  MySQL connection lost, reconnecting...");
                    DB::reconnect();
                    $this->info("  ✓ Reconnected to database");
                }
                
                $this->error("  ✗ Error: {$e->getMessage()}");
                Log::error('RapidAPI card sync failed', [
                    'episode_id' => $episode->episode_id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Rate limiting: 3 seconds between episodes
            // 171 episodes × 3s = ~8.5 minutes total
            // With 300 req/minute limit: ~2-3 API calls per episode = safe margin
            sleep(3);
        }

        $this->info("\nSync completed!");
        $this->info("Episodes processed: {$episodes->count()}");
        $this->info("Total cards: {$totalCards}");
        $this->info("Total price snapshots: {$totalPrices}");
        if ($errors > 0) {
            $this->warn("Errors: {$errors}");
        }

        // Mark pipeline run as success
        $pipelineRun->markSuccess([
            'rows_processed' => $totalCards,
            'rows_created' => $totalPrices,
            'errors_count' => $errors,
        ]);

        return 0;
    }

    private function syncEpisodeCards($episode): array
    {
        $page = 1;
        $cardsProcessed = 0;
        $pricesSaved = 0;
        $today = now()->toDateString();

        do {
            $endpoint = "{$this->baseUrl}/{$episode->game}/episodes/{$episode->episode_id}/cards";
            $params = ['sort' => 'price_highest'];
            if ($page > 1) {
                $params['page'] = $page;
            }

            $response = Http::withHeaders([
                'X-RapidAPI-Key' => $this->apiKey,
                'X-RapidAPI-Host' => config('rapidapi.cardmarket.host')
            ])->timeout(30)
              ->get($endpoint, $params);

            if (!$response->successful()) {
                throw new \Exception("API request failed: {$response->status()}");
            }

            $data = $response->json();
            $cards = $data['data'] ?? [];

            if (empty($cards)) {
                break;
            }

            foreach ($cards as $cardData) {
                // Extract prices from nested structure
                $cardmarketPrices = $cardData['prices']['cardmarket'] ?? [];
                $tcgplayerPrices = $cardData['prices']['tcgplayer'] ?? [];
                
                // Save card info to rapidapi_cards
                DB::table('rapidapi_cards')->updateOrInsert(
                    [
                        'rapidapi_id' => $cardData['id'],
                    ],
                    [
                        'episode_id' => $episode->episode_id,
                        'game' => $episode->game,
                        'name' => $cardData['name'] ?? null,
                        'name_numbered' => $cardData['name_numbered'] ?? null,
                        'slug' => $cardData['slug'] ?? null,
                        'type' => $cardData['type'] ?? null,
                        'card_number' => $cardData['card_number'] ?? null,
                        'hp' => $cardData['hp'] ?? null,
                        'rarity' => $cardData['rarity'] ?? null,
                        'supertype' => $cardData['supertype'] ?? null,
                        'tcgid' => $cardData['tcgid'] ?? null,
                        'image_url' => $cardData['image'] ?? $cardData['image_url'] ?? null,
                        'cardmarket_id' => $cardData['cardmarket_id'] ?? null,
                        'cardmarket_url' => $cardData['links']['cardmarket'] ?? $cardData['cardmarket_url'] ?? null,
                        'tcggo_url' => $cardData['tcggo_url'] ?? null,
                        'episode' => json_encode($cardData['episode'] ?? []),
                        'episode_name' => $cardData['episode']['name'] ?? $episode->name,
                        'episode_slug' => $cardData['episode']['slug'] ?? null,
                        'episode_released_at' => $cardData['episode']['released_at'] ?? null,
                        'artist' => json_encode($cardData['artist'] ?? []),
                        'prices' => json_encode($cardData['prices'] ?? []),
                        'price_eur' => $cardmarketPrices['lowest_near_mint'] ?? null,
                        'links' => json_encode($cardData['links'] ?? []),
                        'raw_data' => json_encode($cardData),
                        'last_synced_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                
                // Save to rapidapi_prices (current prices)
                DB::table('rapidapi_prices')->updateOrInsert(
                    [
                        'card_id' => $cardData['id'],
                        'episode_id' => $episode->episode_id,
                    ],
                    [
                        'game' => $episode->game,
                        'name' => $cardData['name'] ?? null,
                        'number' => $cardData['card_number'] ?? null,
                        'rarity' => $cardData['rarity'] ?? null,
                        'image_url' => $cardData['image_url'] ?? null,
                        'cardmarket_avg' => $cardmarketPrices['lowest_near_mint'] ?? null,
                        'cardmarket_low' => $cardmarketPrices['lowest_near_mint'] ?? null,
                        'cardmarket_high' => null, // Not provided by this API
                        'cardmarket_trend' => null,
                        'tcgplayer_market' => $tcgplayerPrices['market'] ?? null,
                        'tcgplayer_low' => $tcgplayerPrices['low'] ?? null,
                        'tcgplayer_high' => $tcgplayerPrices['high'] ?? null,
                        'tcgplayer_mid' => $tcgplayerPrices['mid'] ?? null,
                        'raw_data' => json_encode($cardData),
                        'updated_at' => now(),
                    ]
                );

                // Save to price history (daily snapshot)
                DB::table('rapidapi_price_history')->updateOrInsert(
                    [
                        'card_id' => $cardData['id'],
                        'snapshot_date' => $today,
                    ],
                    [
                        'episode_id' => $episode->episode_id,
                        'game' => $episode->game,
                        'cardmarket_avg' => $cardmarketPrices['lowest_near_mint'] ?? null,
                        'cardmarket_low' => $cardmarketPrices['lowest_near_mint'] ?? null,
                        'cardmarket_high' => null,
                        'cardmarket_trend' => null,
                        'tcgplayer_market' => $tcgplayerPrices['market'] ?? null,
                        'tcgplayer_low' => $tcgplayerPrices['low'] ?? null,
                        'tcgplayer_high' => $tcgplayerPrices['high'] ?? null,
                        'tcgplayer_mid' => $tcgplayerPrices['mid'] ?? null,
                        'raw_data' => json_encode($cardData),
                        'updated_at' => now(),
                    ]
                );

                $cardsProcessed++;
                $pricesSaved++;
            }

            $page++;
            
            // Check if there are more pages
            $paging = $data['paging'] ?? [];
            $hasMore = isset($paging['current'], $paging['total']) && $paging['current'] < $paging['total'];

        } while ($hasMore);

        return [
            'cards' => $cardsProcessed,
            'prices' => $pricesSaved,
        ];
    }
}
