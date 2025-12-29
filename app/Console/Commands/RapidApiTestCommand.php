<?php

namespace App\Console\Commands;

use App\Services\RapidApi\CardmarketRapidApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RapidApiTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rapidapi:test 
                            {game=pokemon : Game to test (pokemon, mtg, yugioh)}
                            {--pages=1 : Number of pages to fetch}
                            {--save : Save data to database}
                            {--stats : Show statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test RapidAPI Cardmarket integration';

    protected CardmarketRapidApiService $service;

    /**
     * Create a new command instance.
     */
    public function __construct(CardmarketRapidApiService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $game = $this->argument('game');
        $pages = (int) $this->option('pages');
        $save = $this->option('save');
        $showStats = $this->option('stats');

        // Validate game
        if (!in_array($game, ['pokemon', 'mtg', 'yugioh'])) {
            $this->error("Invalid game: {$game}");
            $this->info("Available games: pokemon, mtg, yugioh");
            return self::FAILURE;
        }

        $this->info("🚀 Testing RapidAPI for {$game}...");
        $this->newLine();

        // Fetch data
        $this->line("Fetching {$pages} page(s)...");
        
        $result = $this->service->fetchAllPages($game, $pages);

        if (empty($result['cards'])) {
            $this->error('No cards fetched!');
            return self::FAILURE;
        }

        $this->info("✅ Fetched {$result['total']} cards from {$result['pages_fetched']} pages");
        
        if ($result['total_pages']) {
            $this->line("   Total pages available: {$result['total_pages']}");
        }

        $this->newLine();

        // Show sample card
        $this->showSampleCard($result['cards'][0]);

        // Show statistics
        if ($showStats) {
            $this->newLine();
            $this->showStatistics($result['cards']);
        }

        // Save to database
        if ($save) {
            $this->newLine();
            $this->saveCards($game, $result['cards']);
        }

        return self::SUCCESS;
    }

    /**
     * Show a sample card
     */
    protected function showSampleCard(array $card): void
    {
        $this->info('📄 Sample Card:');
        $this->line("   Name: {$card['name']}");
        $this->line("   RapidAPI ID: {$card['id']}");
        $this->line("   Cardmarket ID: " . ($card['cardmarket_id'] ?? 'N/A'));
        $this->line("   Rarity: " . ($card['rarity'] ?? 'N/A'));
        $this->line("   HP: " . ($card['hp'] ?? 'N/A'));
        
        if (isset($card['episode']['name'])) {
            $this->line("   Expansion: {$card['episode']['name']}");
        }
        
        if (isset($card['prices']['cardmarket']['lowest_near_mint'])) {
            $price = $card['prices']['cardmarket']['lowest_near_mint'];
            $this->line("   Price: €{$price}");
        }
    }

    /**
     * Show statistics
     */
    protected function showStatistics(array $cards): void
    {
        $stats = $this->service->getStatistics($cards);

        $this->info('📊 Statistics:');
        $this->line("   Total Cards: {$stats['total_cards']}");
        
        $this->newLine();
        $this->line('   By Rarity:');
        foreach ($stats['by_rarity'] as $rarity => $count) {
            $this->line("      - {$rarity}: {$count}");
        }

        $this->newLine();
        $this->line('   By Supertype:');
        foreach ($stats['by_supertype'] as $type => $count) {
            $this->line("      - {$type}: {$count}");
        }

        if ($stats['price_ranges']['average'] > 0) {
            $this->newLine();
            $this->line('   Price Ranges (EUR):');
            $this->line("      - Lowest: €{$stats['price_ranges']['lowest']}");
            $this->line("      - Highest: €{$stats['price_ranges']['highest']}");
            $this->line("      - Average: €{$stats['price_ranges']['average']}");
        }
    }

    /**
     * Save cards to database (with improved error handling and batch processing)
     */
    protected function saveCards(string $game, array $cards): void
    {
        $this->info('💾 Saving to database...');

        $logId = DB::table('rapidapi_sync_logs')->insertGetId([
            'game' => $game,
            'status' => 'running',
            'started_at' => now(),
            'cards_fetched' => count($cards),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $inserted = 0;
        $updated = 0;
        $errors = 0;
        $errorMessages = [];

        try {
            // Process cards in smaller batches to avoid memory issues
            $chunks = array_chunk($cards, 50);
            
            foreach ($chunks as $chunkIndex => $chunk) {
                DB::beginTransaction();
                
                try {
                    foreach ($chunk as $card) {
                        try {
                            $data = $this->prepareCardData($game, $card);
                            
                            // Use upsert for better performance and duplicate handling
                            $existing = DB::table('rapidapi_cards')
                                ->where('rapidapi_id', $card['id'])
                                ->exists();

                            if ($existing) {
                                DB::table('rapidapi_cards')
                                    ->where('rapidapi_id', $card['id'])
                                    ->update(array_merge($data, ['updated_at' => now()]));
                                $updated++;
                            } else {
                                DB::table('rapidapi_cards')->insert($data);
                                $inserted++;
                            }
                        } catch (\Exception $cardError) {
                            $errors++;
                            $errorMessages[] = "Card {$card['name']}: {$cardError->getMessage()}";
                            
                            // Log individual card errors but continue processing
                            \Log::warning("Failed to save card", [
                                'card_id' => $card['id'] ?? 'unknown',
                                'card_name' => $card['name'] ?? 'unknown',
                                'error' => $cardError->getMessage()
                            ]);
                        }
                    }
                    
                    DB::commit();
                    
                    // Show progress every 50 cards
                    if (($chunkIndex + 1) % 10 == 0) {
                        $this->line("   Processed " . (($chunkIndex + 1) * 50) . " cards...");
                    }
                    
                } catch (\Exception $chunkError) {
                    DB::rollBack();
                    $errorMessages[] = "Chunk {$chunkIndex}: {$chunkError->getMessage()}";
                    $this->warn("⚠️  Failed to save chunk {$chunkIndex}, continuing...");
                }
            }

            $status = $errors > 0 ? 'completed_with_errors' : 'completed';
            
            DB::table('rapidapi_sync_logs')
                ->where('id', $logId)
                ->update([
                    'status' => $status,
                    'finished_at' => now(),
                    'cards_inserted' => $inserted,
                    'cards_updated' => $updated,
                    'error_message' => $errors > 0 ? implode('; ', array_slice($errorMessages, 0, 5)) : null,
                    'updated_at' => now(),
                ]);

            $this->info("✅ Saved: {$inserted} inserted, {$updated} updated");
            if ($errors > 0) {
                $this->warn("⚠️  {$errors} cards had errors");
            }

        } catch (\Exception $e) {
            DB::table('rapidapi_sync_logs')
                ->where('id', $logId)
                ->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'error_message' => $e->getMessage(),
                    'updated_at' => now(),
                ]);

            $this->error("❌ Failed: {$e->getMessage()}");
            throw $e; // Re-throw for visibility
        }
    }

    /**
     * Prepare card data for database
     */
    protected function prepareCardData(string $game, array $card): array
    {
        return [
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
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
