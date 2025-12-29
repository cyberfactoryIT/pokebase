<?php

namespace App\Console\Commands;

use App\Services\RapidApi\CardmarketRapidApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RapidApiSyncCommand extends Command
{
    protected $signature = 'rapidapi:sync 
                            {game=pokemon : Game to sync}
                            {--strategy=top-episodes : Strategy: top-episodes, expensive-cards, recent-episodes}
                            {--limit=90 : Max API calls to use (save 10 for other operations)}';

    protected $description = 'Smart sync with 100 calls/day limit';

    protected CardmarketRapidApiService $service;

    public function __construct(CardmarketRapidApiService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle(): int
    {
        $game = $this->argument('game');
        $strategy = $this->option('strategy');
        $limit = (int) $this->option('limit');

        $this->info("🚀 Smart Sync for {$game} (Max {$limit} calls)");
        $this->info("Strategy: {$strategy}");
        $this->newLine();

        $callsUsed = 0;

        // Step 1: Get all episodes (1 call)
        $this->line("📋 Fetching episodes list...");
        $episodesResponse = $this->service->fetchEpisodes($game);
        $callsUsed++;
        
        if (empty($episodesResponse['data'])) {
            $this->error('No episodes found!');
            return self::FAILURE;
        }

        $episodes = collect($episodesResponse['data']);
        $this->info("Found {$episodes->count()} episodes");

        // Sync episodes to database
        $this->syncEpisodes($episodes);

        // Step 2: Apply strategy
        $episodesToSync = $this->selectEpisodesbyStrategy($episodes, $strategy, $limit - 1);
        
        $this->newLine();
        $this->info("📦 Syncing {$episodesToSync->count()} episodes...");
        
        foreach ($episodesToSync as $episode) {
            if ($callsUsed >= $limit) {
                $this->warn("⚠️  Reached API call limit ({$limit})");
                break;
            }

            $this->line("  Syncing: {$episode['name']} ({$episode['cards_total']} cards)");
            
            // Calculate pages needed
            $pages = ceil($episode['cards_total'] / 20);
            $pagesToFetch = min($pages, $limit - $callsUsed);
            
            $cardsSynced = 0;
            for ($page = 1; $page <= $pagesToFetch; $page++) {
                $cardsResponse = $this->service->fetchEpisodeCards($game, $episode['id'], 'price_highest', $page);
                $callsUsed++;
                
                if (!empty($cardsResponse['data'])) {
                    $this->saveCards($game, $cardsResponse['data']);
                    $cardsSynced += count($cardsResponse['data']);
                }
                
                if ($callsUsed >= $limit) {
                    break;
                }
                
                // Rate limiting
                usleep(100000); // 0.1s between calls
            }
            
            $this->info("    ✅ Synced {$cardsSynced} cards (used {$callsUsed} calls)");
        }

        $this->newLine();
        $this->info("🎉 Sync completed!");
        $this->line("Total API calls used: {$callsUsed}/{$limit}");
        $this->line("Total cards in DB: " . DB::table('rapidapi_cards')->count());

        return self::SUCCESS;
    }

    protected function selectEpisodesbyStrategy($episodes, string $strategy, int $maxCalls): \Illuminate\Support\Collection
    {
        return match($strategy) {
            'top-episodes' => $episodes
                ->sortByDesc(fn($e) => $e['prices']['cardmarket']['total'])
                ->take(max(1, floor($maxCalls / 5))), // ~5 calls per episode (1 page)
                
            'recent-episodes' => $episodes
                ->sortByDesc('released_at')
                ->take(max(1, floor($maxCalls / 5))),
                
            'expensive-cards' => $episodes
                ->sortByDesc(fn($e) => $e['prices']['cardmarket']['total'])
                ->take(max(1, floor($maxCalls / 10))), // Fewer episodes, more pages (2 pages)
                
            default => $episodes->take(max(1, floor($maxCalls / 5))),
        };
    }

    protected function syncEpisodes($episodes): void
    {
        foreach ($episodes as $episode) {
            DB::table('rapidapi_episodes')->updateOrInsert(
                ['episode_id' => $episode['id']],
                [
                    'episode_id' => $episode['id'],
                    'game' => $episode['game']['slug'],
                    'name' => $episode['name'],
                    'slug' => $episode['slug'],
                    'code' => $episode['code'] ?? null,
                    'released_at' => $episode['released_at'],
                    'logo_url' => $episode['logo'] ?? null,
                    'cards_total' => $episode['cards_total'],
                    'cards_printed_total' => $episode['cards_printed_total'],
                    'series_id' => $episode['series']['id'] ?? null,
                    'series_name' => $episode['series']['name'] ?? null,
                    'cardmarket_total_value' => $episode['prices']['cardmarket']['total'] ?? 0,
                    'tcgplayer_total_value' => $episode['prices']['tcgplayer']['total'] ?? 0,
                    'raw_data' => json_encode($episode),
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }
    }

    protected function saveCards(string $game, array $cards): void
    {
        foreach ($cards as $card) {
            DB::table('rapidapi_cards')->updateOrInsert(
                ['rapidapi_id' => $card['id']],
                [
                    'rapidapi_id' => $card['id'],
                    'cardmarket_id' => $card['cardmarket_id'] ?? null,
                    'game' => $game,
                    'name' => $card['name'],
                    'name_numbered' => $card['name_numbered'] ?? null,
                    'slug' => $card['slug'],
                    'type' => $card['type'] ?? null,
                    'card_number' => $card['card_number'] ?? null,
                    'hp' => $card['hp'] ?? null,
                    'rarity' => $card['rarity'] ?? null,
                    'supertype' => $card['supertype'] ?? null,
                    'tcgid' => $card['tcgid'] ?? null,
                    'episode' => isset($card['episode']) ? json_encode($card['episode']) : null,
                    'episode_id' => $card['episode']['id'] ?? null,
                    'episode_name' => $card['episode']['name'] ?? null,
                    'episode_slug' => $card['episode']['slug'] ?? null,
                    'episode_released_at' => isset($card['episode']['released_at']) ? $card['episode']['released_at'] : null,
                    'artist' => isset($card['artist']) ? json_encode($card['artist']) : null,
                    'prices' => isset($card['prices']) ? json_encode($card['prices']) : null,
                    'price_eur' => $card['prices']['cardmarket']['lowest_near_mint'] ?? null,
                    'image_url' => $card['image'] ?? null,
                    'tcggo_url' => $card['tcggo_url'] ?? null,
                    'links' => isset($card['links']) ? json_encode($card['links']) : null,
                    'cardmarket_url' => isset($card['cardmarket_id']) 
                        ? "https://www.cardmarket.com/en/Pokemon/Products/Singles/{$card['cardmarket_id']}"
                        : null,
                    'raw_data' => json_encode($card),
                    'last_synced_at' => now(),
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }
    }
}
