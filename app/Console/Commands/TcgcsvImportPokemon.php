<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Tcgcsv\TcgcsvImportService;
use App\Services\Tcgcsv\TcgcsvClient;
use App\Models\PipelineRun;

class TcgcsvImportPokemon extends Command
{
    protected $signature = 'tcgcsv:import-pokemon 
                            {--groupId= : Import only specific group ID}
                            {--only= : Import only specific type: groups|products|prices|all}';
    
    protected $description = 'Import Pokemon TCG data from tcgcsv.com (TCGplayer)';

    public function handle(): int
    {
        $pipelineRun = PipelineRun::start('tcgcsv:import-pokemon', [
            'group_id' => $this->option('groupId'),
            'only' => $this->option('only') ?? 'all',
        ]);

        $this->info('╔════════════════════════════════════════════════════════╗');
        $this->info('║  TCGCSV Pokemon Import (TCGplayer Category 3)         ║');
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
            $client = new TcgcsvClient();
            $service = new TcgcsvImportService($client);
            
            $this->line("Run ID: <fg=cyan>{$service->getRunId()}</>");
            $this->line("Category: <fg=cyan>Pokemon (ID: 3)</>");
            
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
            
            $stats = null;
            
            if ($only === 'all') {
                $stats = $this->importAll($service, $groupId, $options);
            } elseif ($only === 'groups') {
                $stats = $this->importGroups($service);
            } elseif ($only === 'products') {
                $stats = $this->importProducts($service, $groupId);
            } elseif ($only === 'prices') {
                $stats = $this->importPrices($service, $groupId);
            }
            
            $duration = round(microtime(true) - $startTime, 2);
            
            $this->newLine();
            $this->info("✓ Import completed in {$duration}s");
            
            // Mark pipeline run as success
            if ($stats) {
                $rowsProcessed = 0;
                $rowsCreated = 0;
                $rowsUpdated = 0;
                $errorsCount = 0;
                
                if (isset($stats['groups'])) {
                    $rowsProcessed += $stats['groups']['total'] ?? 0;
                    $rowsCreated += $stats['groups']['new'] ?? 0;
                    $rowsUpdated += $stats['groups']['updated'] ?? 0;
                    $errorsCount += $stats['groups']['failed'] ?? 0;
                }
                
                if (isset($stats['products'])) {
                    $rowsProcessed += $stats['products']['total'] ?? 0;
                    $rowsCreated += $stats['products']['new'] ?? 0;
                    $rowsUpdated += $stats['products']['updated'] ?? 0;
                    $errorsCount += $stats['products']['failed'] ?? 0;
                }
                
                if (isset($stats['prices'])) {
                    $rowsProcessed += $stats['prices']['total'] ?? 0;
                    $rowsCreated += $stats['prices']['new'] ?? 0;
                    $rowsUpdated += $stats['prices']['updated'] ?? 0;
                    $errorsCount += $stats['prices']['failed'] ?? 0;
                }
                
                $pipelineRun->markSuccess([
                    'rows_processed' => $rowsProcessed,
                    'rows_created' => $rowsCreated,
                    'rows_updated' => $rowsUpdated,
                    'errors_count' => $errorsCount,
                ]);
            } else {
                $pipelineRun->markSuccess();
            }
            
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            
            $pipelineRun->markFailed($e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            
            return self::FAILURE;
        }
    }
    
    protected function importAll(TcgcsvImportService $service, ?int $groupId, array $options = []): array
    {
        $this->line('Starting full import...');
        $this->newLine();
        
        $stats = $service->importAll($groupId, $options);
        
        $this->displayResults($stats);
        
        return $stats;
    }
    
    protected function importGroups(TcgcsvImportService $service): array
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
        
        return ['groups' => $stats];
    }
    
    protected function importProducts(TcgcsvImportService $service, ?int $groupId): array
    {
        if (!$groupId) {
            $this->error('Must specify --groupId when using --only=products');
            return [];
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
        
        return ['products' => $stats];
    }
    
    protected function importPrices(TcgcsvImportService $service, ?int $groupId): array
    {
        if (!$groupId) {
            $this->error('Must specify --groupId when using --only=prices');
            return [];
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
        
        return ['prices' => $stats];
    }
    
    protected function displayResults(array $stats): void
    {
        $this->line('═══════════════════════════════════════════════════════');
        $this->line('<fg=bright-white>IMPORT SUMMARY</>');
        $this->line('═══════════════════════════════════════════════════════');
        $this->newLine();
        
        if (isset($stats['groups'])) {
            $this->line('<fg=bright-cyan>Groups:</>');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total', $stats['groups']['total']],
                    ['New', "<fg=green>{$stats['groups']['new']}</>"],
                    ['Updated', "<fg=yellow>{$stats['groups']['updated']}</>"],
                    ['Failed', $stats['groups']['failed'] > 0 ? "<fg=red>{$stats['groups']['failed']}</>" : $stats['groups']['failed']],
                ]
            );
            $this->newLine();
        }
        
        $this->line('<fg=bright-cyan>Products:</>');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total', $stats['products']['total']],
                ['New', "<fg=green>{$stats['products']['new']}</>"],
                ['Updated', "<fg=yellow>{$stats['products']['updated']}</>"],
                ['Failed', $stats['products']['failed'] > 0 ? "<fg=red>{$stats['products']['failed']}</>" : $stats['products']['failed']],
            ]
        );
        $this->newLine();
        
        $this->line('<fg=bright-cyan>Prices:</>');
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
