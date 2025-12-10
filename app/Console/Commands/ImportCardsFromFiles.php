<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PokemonImportService;
use Illuminate\Support\Str;

class ImportCardsFromFiles extends Command
{
    protected $signature = 'pokemon:import-cards-from-files 
                            {--dir=storage/app/pokemon_cards : Directory with JSON files}
                            {--force : Force reimport of existing cards}';
    
    protected $description = 'Import Pokemon TCG cards from downloaded JSON files';

    public function handle(PokemonImportService $importService): int
    {
        $dir = $this->option('dir');
        $force = $this->option('force');
        
        if (!is_dir($dir)) {
            $this->error("Directory not found: {$dir}");
            return static::FAILURE;
        }
        
        $files = glob($dir . '/*.json');
        
        if (empty($files)) {
            $this->error("No JSON files found in {$dir}");
            return static::FAILURE;
        }
        
        $this->info('Importing Pokemon TCG cards from ' . count($files) . ' files...');
        
        $totalStats = [
            'processed' => 0,
            'new' => 0,
            'updated' => 0,
            'failed' => 0,
        ];
        
        foreach ($files as $file) {
            $this->line("Processing: " . basename($file));
            
            $json = file_get_contents($file);
            $data = json_decode($json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->warn("  Skipping invalid JSON file");
                continue;
            }
            
            $cards = $data['data'] ?? [];
            
            if (empty($cards)) {
                $this->warn("  No cards in file");
                continue;
            }
            
            // Importa le carte usando il metodo protetto del service
            $stats = $this->importCardsFromData($cards, $force);
            
            $totalStats['processed'] += $stats['processed'];
            $totalStats['new'] += $stats['new'];
            $totalStats['updated'] += $stats['updated'];
            $totalStats['failed'] += $stats['failed'];
            
            $this->info("  Imported: {$stats['new']} new, {$stats['updated']} updated, {$stats['failed']} failed");
        }
        
        $this->newLine();
        $this->info('Import completed!');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Cards Processed', $totalStats['processed']],
                ['New Cards', $totalStats['new']],
                ['Updated Cards', $totalStats['updated']],
                ['Failed Cards', $totalStats['failed']],
            ]
        );
        
        return static::SUCCESS;
    }
    
    private function importCardsFromData(array $cards, bool $force): array
    {
        $gameId = \DB::table('games')->where('code', 'pokemon')->value('id');
        
        if (!$gameId) {
            throw new \RuntimeException('Pokemon game not found in `games` table.');
        }
        
        $stats = [
            'processed' => 0,
            'new' => 0,
            'updated' => 0,
            'failed' => 0,
        ];
        
        foreach ($cards as $card) {
            try {
                $set = $card['set'] ?? [];
                
                $imageUrl = null;
                if (isset($card['images']['small'])) {
                    $imageUrl = $card['images']['small'];
                } elseif (isset($card['images']['large'])) {
                    $imageUrl = $card['images']['large'];
                }
                
                $extra = [
                    'supertype' => $card['supertype'] ?? null,
                    'subtypes' => $card['subtypes'] ?? null,
                    'hp' => $card['hp'] ?? null,
                    'types' => $card['types'] ?? null,
                    'tcgplayer' => $card['tcgplayer'] ?? null,
                    'cardmarket' => $card['cardmarket'] ?? null,
                    'legalities' => $card['legalities'] ?? null,
                ];
                
                $existing = \DB::table('card_catalog')
                    ->where('game_id', $gameId)
                    ->where('set_code', $set['id'] ?? null)
                    ->where('collector_number', $card['number'] ?? null)
                    ->where('name', $card['name'] ?? null)
                    ->first();
                
                $data = [
                    'game_id' => $gameId,
                    'name' => $card['name'] ?? null,
                    'set_name' => $set['name'] ?? null,
                    'set_code' => $set['id'] ?? null,
                    'collector_number' => $card['number'] ?? null,
                    'rarity' => $card['rarity'] ?? null,
                    'type_line' => $card['supertype'] ?? null,
                    'image_url' => $imageUrl,
                    'extra_data' => json_encode($extra),
                    'updated_at' => now(),
                ];
                
                if ($existing && !$force) {
                    \DB::table('card_catalog')
                        ->where('id', $existing->id)
                        ->update($data);
                    $stats['updated']++;
                } else {
                    $data['created_at'] = now();
                    if ($existing && $force) {
                        \DB::table('card_catalog')
                            ->where('id', $existing->id)
                            ->update($data);
                        $stats['updated']++;
                    } else {
                        \DB::table('card_catalog')->insert($data);
                        $stats['new']++;
                    }
                }
                
                $stats['processed']++;
                
            } catch (\Throwable $e) {
                $stats['failed']++;
                \Log::error('Failed to import card from file', [
                    'card' => $card,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return $stats;
    }
}
