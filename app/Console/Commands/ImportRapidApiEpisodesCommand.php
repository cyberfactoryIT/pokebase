<?php

namespace App\Console\Commands;

use App\Services\RapidApi\CardmarketRapidApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportRapidApiEpisodesCommand extends Command
{
    protected $signature = 'rapidapi:import-episodes 
                            {game=pokemon : Game to import episodes for}
                            {--sync-groups : Also sync episodes to TCGCSV groups}';

    protected $description = 'Import only episodes/sets list from RapidAPI (no cards)';

    protected CardmarketRapidApiService $service;

    public function __construct(CardmarketRapidApiService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle(): int
    {
        $game = $this->argument('game');
        $syncGroups = $this->option('sync-groups');

        $this->info("📺 Importing {$game} episodes/sets...");
        $this->newLine();

        try {
            // Fetch episodes (1 API call for all episodes)
            $this->line("🌐 Fetching episodes from RapidAPI...");
            $response = $this->service->fetchEpisodes($game);

            if (empty($response['data'])) {
                $this->error('No episodes found!');
                return self::FAILURE;
            }

            $episodes = $response['data'];
            $this->info("✅ Fetched {$response['total']} episodes");
            $this->newLine();

            // Save to database
            $this->line("💾 Saving to database...");
            $progressBar = $this->output->createProgressBar(count($episodes));
            $progressBar->start();

            $inserted = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($episodes as $episode) {
                // Skip episodes without release date
                if (empty($episode['released_at'])) {
                    $skipped++;
                    $progressBar->advance();
                    continue;
                }

                $exists = DB::table('rapidapi_episodes')
                    ->where('episode_id', $episode['id'])
                    ->exists();

                DB::table('rapidapi_episodes')->updateOrInsert(
                    ['episode_id' => $episode['id']],
                    [
                        'episode_id' => $episode['id'],
                        'game' => $game,
                        'name' => $episode['name'],
                        'slug' => $episode['slug'],
                        'code' => $episode['code'] ?? null,
                        'released_at' => $episode['released_at'],
                        'logo_url' => $episode['logo'] ?? null,
                        'cards_total' => $episode['cards_total'] ?? 0,
                        'cards_printed_total' => $episode['cards_printed_total'] ?? 0,
                        'series_id' => $episode['series']['id'] ?? null,
                        'series_name' => $episode['series']['name'] ?? null,
                        'cardmarket_total_value' => $episode['prices']['cardmarket']['total'] ?? 0,
                        'raw_data' => json_encode($episode),
                        'updated_at' => now(),
                        'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                    ]
                );

                $exists ? $updated++ : $inserted++;
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            // Summary
            $this->info("📊 Summary:");
            $this->line("   Total episodes: {$response['total']}");
            $this->line("   Inserted: {$inserted}");
            $this->line("   Updated: {$updated}");
            $this->line("   Skipped: {$skipped}");
            $this->line("   API calls used: 1");

            // Sync to groups if requested
            if ($syncGroups) {
                $this->newLine();
                $this->line("🔗 Syncing episodes to TCGCSV groups...");
                $this->call('rapidapi:map-episodes');
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
