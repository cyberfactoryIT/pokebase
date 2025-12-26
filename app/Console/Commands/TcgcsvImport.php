<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Tcgcsv\TcgcsvImportService;
use App\Services\Tcgcsv\TcgcsvClient;
use App\Models\Game;

class TcgcsvImport extends Command
{
    protected $signature = 'tcgcsv:import 
                            {--game= : Game code (pokemon, mtg, yugioh)}
                            {--groupId= : Import only specific group ID}
                            {--only= : Import only specific type: groups|products|prices|all}';
    
    protected $description = 'Import TCG data from tcgcsv.com for any game';

    public function handle(): int
    {
        $gameCode = $this->option('game');
        
        if (!$gameCode) {
            $this->error('Please specify --game option (pokemon, mtg, yugioh)');
            $this->line('Example: php artisan tcgcsv:import --game=mtg --only=groups');
            return self::FAILURE;
        }
        
        // Get game from database
        $game = Game::where('code', $gameCode)->first();
        
        if (!$game) {
            $this->error("Game '{$gameCode}' not found in database.");
            $this->line('Available games: pokemon, mtg, yugioh');
            return self::FAILURE;
        }
        
        if (!$game->tcgcsv_category_id) {
            $this->error("Game '{$gameCode}' does not have a TCGCSV category ID configured.");
            return self::FAILURE;
        }
        
        $this->info('╔════════════════════════════════════════════════════════╗');
        $this->info("║  TCGCSV Import - {$game->name}");
        $this->info('╚════════════════════════════════════════════════════════╝');
        $this->newLine();
        
        $groupId = $this->option('groupId');
        $only = $this->option('only') ?? 'all';
        
        if (!in_array($only, ['groups', 'products', 'prices', 'all'])) {
            $this->error('Invalid --only option. Must be: groups, products, prices, or all');
            return self::FAILURE;
        }
        
        if ($groupId && $only === 'groups') {
            $this->error('Cannot specify --groupId with --only=groups');
            return self::FAILURE;
        }
        
        try {
            // Create client and service with game's category ID
            $client = new TcgcsvClient($game->tcgcsv_category_id);
            $service = new TcgcsvImportService($client, $game->id);
            
            $this->line("Run ID: <fg=cyan>{$service->getRunId()}</>");
            $this->line("Game: <fg=cyan>{$game->name}</>");
            $this->line("TCGCSV Category: <fg=cyan>{$game->tcgcsv_category_id}</>");
            
            if ($groupId) {
                $this->line("Target: <fg=cyan>Group {$groupId}</>");
            } else {
                $this->line("Target: <fg=cyan>All groups</>");
            }
            
            $this->line("Mode: <fg=cyan>{$only}</>");
            $this->newLine();
            
            $startTime = microtime(true);
            
            $options = [
                'groupId' => $groupId,
                'only' => $only,
            ];
            
            if ($only === 'all') {
                $this->importAll($service, $groupId, $options);
            } elseif ($only === 'groups') {
                $this->importGroups($service);
            } elseif ($only === 'products') {
                $this->importProducts($service, $groupId);
            } elseif ($only === 'prices') {
                $this->importPrices($service, $groupId);
            }
            
            $duration = round(microtime(true) - $startTime, 2);
            
            $this->newLine();
            $this->info("✓ Import completed in {$duration}s");
            
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return self::FAILURE;
        }
    }
    
    protected function importAll(TcgcsvImportService $service, ?int $groupId, array $options = []): void
    {
        $this->line('Starting full import...');
        $this->newLine();
        
        $stats = $service->importAll($groupId, $options);
        
        $this->displayResults($stats);
    }
    
    protected function importGroups(TcgcsvImportService $service): void
    {
        $this->line('Importing groups...');
        $this->newLine();
        
        $stats = $service->importGroups();
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total', $stats['total']],
                ['New', "<fg=green>{$stats['new']}</>"],
                ['Updated', "<fg=yellow>{$stats['updated']}</>"],
                ['Failed', $stats['failed'] > 0 ? "<fg=red>{$stats['failed']}</>" : $stats['failed']],
            ]
        );
    }
    
    protected function importProducts(TcgcsvImportService $service, ?int $groupId): void
    {
        if (!$groupId) {
            $this->error('Must specify --groupId when using --only=products');
            return;
        }
        
        $this->line("Importing products for group {$groupId}...");
        $this->newLine();
        
        $stats = $service->importProductsByGroup($groupId);
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Group ID', $stats['group_id']],
                ['Total', $stats['total']],
                ['New', "<fg=green>{$stats['new']}</>"],
                ['Updated', "<fg=yellow>{$stats['updated']}</>"],
                ['Failed', $stats['failed'] > 0 ? "<fg=red>{$stats['failed']}</>" : $stats['failed']],
            ]
        );
    }
    
    protected function importPrices(TcgcsvImportService $service, ?int $groupId): void
    {
        if (!$groupId) {
            $this->error('Must specify --groupId when using --only=prices');
            return;
        }
        
        $this->line("Importing prices for group {$groupId}...");
        $this->newLine();
        
        $stats = $service->importPricesByGroup($groupId);
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Group ID', $stats['group_id']],
                ['Total', $stats['total']],
                ['New', "<fg=green>{$stats['new']}</>"],
                ['Updated', "<fg=yellow>{$stats['updated']}</>"],
                ['Failed', $stats['failed'] > 0 ? "<fg=red>{$stats['failed']}</>" : $stats['failed']],
            ]
        );
    }
    
    protected function displayResults(array $stats): void
    {
        $this->newLine();
        $this->line('═══════════════════════════════════════════════════════');
        $this->info('Import Results:');
        $this->line('═══════════════════════════════════════════════════════');
        
        if (isset($stats['groups'])) {
            $this->line("\n<fg=bright-blue>Groups:</>");
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total', $stats['groups']['total']],
                    ['New', "<fg=green>{$stats['groups']['new']}</>"],
                    ['Updated', "<fg=yellow>{$stats['groups']['updated']}</>"],
                    ['Failed', $stats['groups']['failed'] > 0 ? "<fg=red>{$stats['groups']['failed']}</>" : $stats['groups']['failed']],
                ]
            );
        }
        
        if (isset($stats['products'])) {
            $this->line("\n<fg=bright-blue>Products:</>");
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total', $stats['products']['total']],
                    ['New', "<fg=green>{$stats['products']['new']}</>"],
                    ['Updated', "<fg=yellow>{$stats['products']['updated']}</>"],
                    ['Failed', $stats['products']['failed'] > 0 ? "<fg=red>{$stats['products']['failed']}</>" : $stats['products']['failed']],
                ]
            );
        }
        
        if (isset($stats['prices'])) {
            $this->line("\n<fg=bright-blue>Prices:</>");
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total', $stats['prices']['total']],
                    ['New', "<fg=green>{$stats['prices']['new']}</>"],
                    ['Updated', "<fg=yellow>{$stats['prices']['updated']}</>"],
                    ['Failed', $stats['prices']['failed'] > 0 ? "<fg=red>{$stats['prices']['failed']}</>" : $stats['prices']['failed']],
                ]
            );
        }
    }
}
