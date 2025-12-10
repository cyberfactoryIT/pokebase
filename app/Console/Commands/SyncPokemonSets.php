<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\PokemonSet;

class SyncPokemonSets extends Command
{
    protected $signature = 'pokemon:sync-sets';
    protected $description = 'Sync Pokemon TCG sets from the API';

    public function handle(): int
    {
        $this->info('Syncing Pokemon TCG sets from API...');

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'X-Api-Key' => config('pokemon.api_key'),
            ])
            ->timeout(120)
            ->retry(3, 2000)
            ->get(config('pokemon.base_url') . '/sets', [
                'pageSize' => 250,
            ]);

            if (!$response->successful()) {
                $this->error('Failed to fetch sets from API');
                return static::FAILURE;
            }

            $data = $response->json();
            $sets = $data['data'] ?? [];
            $totalCount = $data['totalCount'] ?? count($sets);

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
