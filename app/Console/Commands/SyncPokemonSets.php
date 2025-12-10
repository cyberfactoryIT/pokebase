<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PokemonSet;

class SyncPokemonSets extends Command
{
    protected $signature = 'pokemon:sync-sets';
    protected $description = 'Sync Pokemon TCG sets from the API';

    public function handle(): int
    {
        $this->info('Syncing Pokemon TCG sets from API...');

        try {
            // L'endpoint /sets non supporta paginazione, ritorna tutti i set in una chiamata
            $url = config('pokemon.base_url') . '/sets';
            
            $this->line("Fetching from: {$url}");
            
            $maxRetries = 5;
            $attempt = 0;
            $success = false;
            $json = null;
            
            while ($attempt < $maxRetries && !$success) {
                $attempt++;
                
                if ($attempt > 1) {
                    $delay = 5 * pow(2, $attempt - 2); // 5s, 10s, 20s, 40s
                    $this->line("Retry {$attempt}/{$maxRetries} after {$delay}s...");
                    sleep($delay);
                }
                
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT => 60, // Aumentato per l'endpoint sets
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_HTTPHEADER => [
                        'Accept: application/json',
                        'X-Api-Key: ' . config('pokemon.api_key'),
                    ],
                ]);
                
                $json = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);
                
                if ($curlError) {
                    $this->warn("cURL error (attempt {$attempt}): {$curlError}");
                    continue;
                }
                
                if ($httpCode !== 200) {
                    $this->warn("HTTP {$httpCode} (attempt {$attempt})");
                    continue;
                }
                
                $success = true;
            }
            
            if (!$success) {
                $this->error("Failed to fetch sets after {$maxRetries} attempts");
                return static::FAILURE;
            }
            
            $data = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Invalid JSON response: ' . json_last_error_msg());
                return static::FAILURE;
            }
            
            $sets = $data['data'] ?? [];
            $totalCount = $data['count'] ?? count($sets);

            $this->info("Found {$totalCount} sets");

            $newSets = 0;
            $updatedSets = 0;

            foreach ($sets as $setData) {
                    $set = PokemonSet::updateOrCreate(
                        ['set_id' => $setData['id']],
                        [
                            'name' => $setData['name'],
                            'series' => $setData['series'] ?? null,
                            'printed_total' => $setData['printedTotal'] ?? null,
                            'total' => $setData['total'] ?? null,
                            'ptcgo_code' => $setData['ptcgoCode'] ?? null,
                            'release_date' => isset($setData['releaseDate']) 
                                ? \Carbon\Carbon::createFromFormat('Y/m/d', $setData['releaseDate'])
                                : null,
                            'api_updated_at' => isset($setData['updatedAt'])
                                ? \Carbon\Carbon::createFromFormat('Y/m/d H:i:s', $setData['updatedAt'])
                                : null,
                            'symbol_url' => $setData['images']['symbol'] ?? null,
                            'logo_url' => $setData['images']['logo'] ?? null,
                            'legalities' => $setData['legalities'] ?? null,
                        ]
                    );

                    if ($set->wasRecentlyCreated) {
                        $newSets++;
                    } else {
                        $updatedSets++;
                    }
                }

            $this->newLine();
            $this->info('Sync completed successfully!');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Sets', $totalCount],
                    ['New Sets', $newSets],
                    ['Updated Sets', $updatedSets],
                ]
            );

            return static::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('Error syncing sets: ' . $e->getMessage());
            return static::FAILURE;
        }
    }
}
