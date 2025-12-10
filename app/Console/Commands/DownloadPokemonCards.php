<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DownloadPokemonCards extends Command
{
    protected $signature = 'pokemon:download-cards 
                            {--set= : Download only specific set code}
                            {--output-dir=storage/app/pokemon_cards : Output directory for JSON files}';
    
    protected $description = 'Download all Pokemon TCG cards to JSON files via curl';

    public function handle(): int
    {
        $setCode = $this->option('set');
        $outputDir = $this->option('output-dir');
        
        // Crea la directory se non esiste
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        
        $this->info('Downloading Pokemon TCG cards...');
        if ($setCode) {
            $this->info("Set filter: {$setCode}");
        }
        
        $baseUrl = config('pokemon.base_url');
        $apiKey = config('pokemon.api_key');
        $pageSize = 250; // Massimo per scaricare meno file
        
        $page = 1;
        $totalCards = 0;
        $filesCreated = 0;
        
        do {
            $params = [
                'page' => $page,
                'pageSize' => $pageSize,
            ];
            
            if ($setCode) {
                $params['q'] = "set.id:{$setCode}";
            }
            
            $url = "{$baseUrl}/cards?" . http_build_query($params);
            $outputFile = $setCode 
                ? "{$outputDir}/{$setCode}_page_{$page}.json"
                : "{$outputDir}/cards_page_{$page}.json";
            
            $this->line("Downloading page {$page}...");
            
            // Usa curl da shell
            $cmd = sprintf(
                'curl -s -H "X-Api-Key: %s" "%s" -o "%s"',
                escapeshellarg($apiKey),
                escapeshellarg($url),
                escapeshellarg($outputFile)
            );
            
            exec($cmd, $output, $returnCode);
            
            if ($returnCode !== 0) {
                $this->error("Failed to download page {$page}");
                break;
            }
            
            // Verifica il file
            if (!file_exists($outputFile)) {
                $this->error("Output file not created for page {$page}");
                break;
            }
            
            $data = json_decode(file_get_contents($outputFile), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error("Invalid JSON for page {$page}");
                unlink($outputFile);
                break;
            }
            
            $cards = $data['data'] ?? [];
            $count = count($cards);
            
            if ($count === 0) {
                // Nessuna carta in questa pagina, cancella il file e esci
                unlink($outputFile);
                break;
            }
            
            $totalCards += $count;
            $filesCreated++;
            
            $this->info("  Saved {$count} cards to {$outputFile}");
            
            // Pausa per non sovraccaricare l'API
            sleep(1);
            
            $page++;
            
        } while (true);
        
        $this->newLine();
        $this->info('Download completed!');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Cards Downloaded', $totalCards],
                ['Files Created', $filesCreated],
                ['Output Directory', $outputDir],
            ]
        );
        
        $this->newLine();
        $this->info("Import with: php artisan pokemon:import-cards-from-files --dir={$outputDir}");
        
        return static::SUCCESS;
    }
}
