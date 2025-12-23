<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PokemonSet;
use Illuminate\Support\Facades\Http;

class CheckPokemonUpdates extends Command
{
    protected $signature = 'pokemon:check-updates 
                            {--set= : Check specific set only}';
    
    protected $description = 'Check for updates in Pokemon TCG data';

    public function handle(): int
    {
        $apiKey = config('pokemon.api_key');
        $baseUrl = config('pokemon.base_url');
        
        $this->info('Checking for Pokemon TCG updates...');
        
        // Check sets updates
        $this->line("\nChecking sets...");
        
        try {
            $response = Http::timeout(60)
                ->withHeaders(['X-Api-Key' => $apiKey])
                ->get("{$baseUrl}/sets");
            
            if (!$response->successful()) {
                $this->error('Failed to fetch sets from API');
                return static::FAILURE;
            }
            
            $apiSets = collect($response->json()['data']);
            $dbSets = PokemonSet::all()->keyBy('set_id');
            
            $newSets = [];
            $updatedSets = [];
            
            foreach ($apiSets as $apiSet) {
                $setId = $apiSet['id'];
                $apiUpdatedAt = $apiSet['updatedAt'] ?? null;
                
                if (!$dbSets->has($setId)) {
                    $newSets[] = $apiSet['name'];
                } elseif ($apiUpdatedAt && $dbSets[$setId]->api_updated_at !== $apiUpdatedAt) {
                    $updatedSets[] = [
                        'name' => $apiSet['name'],
                        'old' => $dbSets[$setId]->api_updated_at,
                        'new' => $apiUpdatedAt,
                    ];
                }
            }
            
            if (empty($newSets) && empty($updatedSets)) {
                $this->info('✓ All sets are up to date!');
            } else {
                if (!empty($newSets)) {
                    $this->warn("\nNew sets found (" . count($newSets) . "):");
                    foreach ($newSets as $set) {
                        $this->line("  • {$set}");
                    }
                }
                
                if (!empty($updatedSets)) {
                    $this->warn("\nUpdated sets found (" . count($updatedSets) . "):");
                    foreach ($updatedSets as $set) {
                        $this->line("  • {$set['name']}");
                        $this->line("    Old: {$set['old']} → New: {$set['new']}");
                    }
                }
                
                $this->newLine();
                $this->info('Run "php artisan pokemon:sync-sets" to update sets');
            }
            
        } catch (\Exception $e) {
            $this->error('Error checking sets: ' . $e->getMessage());
            return static::FAILURE;
        }
        
        // Check specific set cards if requested
        if ($setId = $this->option('set')) {
            $this->newLine();
            $this->checkSetCards($setId, $apiKey, $baseUrl);
        }
        
        return static::SUCCESS;
    }
    
    private function checkSetCards(string $setId, string $apiKey, string $baseUrl): void
    {
        $this->line("Checking cards for set: {$setId}");
        
        $set = PokemonSet::where('set_id', $setId)->first();
        
        if (!$set) {
            $this->error("Set {$setId} not found in database");
            return;
        }
        
        try {
            $response = Http::timeout(60)
                ->withHeaders(['X-Api-Key' => $apiKey])
                ->get("{$baseUrl}/cards", [
                    'q' => "set.id:{$setId}",
                    'pageSize' => 250,
                ]);
            
            if (!$response->successful()) {
                $this->error('Failed to fetch cards from API');
                return;
            }
            
            $apiCardCount = $response->json()['totalCount'] ?? 0;
            $dbCardCount = \App\Models\Card::where('set_code', $setId)->count();
            
            $this->table(
                ['Metric', 'Database', 'API', 'Status'],
                [
                    [
                        'Total Cards',
                        $dbCardCount,
                        $apiCardCount,
                        $dbCardCount === $apiCardCount ? '✓' : '⚠ Mismatch'
                    ],
                    [
                        'Last Import',
                        $set->last_import_at ? $set->last_import_at->diffForHumans() : 'Never',
                        '-',
                        '-'
                    ],
                ]
            );
            
            if ($dbCardCount !== $apiCardCount) {
                $this->warn("\nCards mismatch! Run import to sync:");
                $this->line("php artisan pokemon:import-all --set={$setId} --force");
            }
            
        } catch (\Exception $e) {
            $this->error('Error checking cards: ' . $e->getMessage());
        }
    }
}
