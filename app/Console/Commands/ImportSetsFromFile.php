<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\PokemonSet;

class ImportSetsFromFile extends Command
{
    protected $signature = 'pokemon:import-sets-from-file {file=pokemon_sets.json}';
    protected $description = 'Import Pokemon TCG sets from a JSON file';

    public function handle(): int
    {
        $filename = $this->argument('file');
        
        if (!Storage::exists($filename)) {
            $this->error("File not found: storage/app/{$filename}");
            return static::FAILURE;
        }

        $this->info("Reading sets from: storage/app/{$filename}");

        try {
            $json = Storage::get($filename);
            $data = json_decode($json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Invalid JSON: ' . json_last_error_msg());
                return static::FAILURE;
            }
            
            $sets = $data['data'] ?? [];
            $totalCount = $data['count'] ?? count($sets);

            $this->info("Found {$totalCount} sets in file");

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
            $this->info('Import completed successfully!');
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
            $this->error('Error importing sets: ' . $e->getMessage());
            return static::FAILURE;
        }
    }
}
