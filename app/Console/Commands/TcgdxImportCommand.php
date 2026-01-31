<?php

namespace App\Console\Commands;

use App\Models\PipelineRun;
use App\Models\Tcgdx\TcgdxCard;
use App\Models\Tcgdx\TcgdxImportRun;
use App\Models\Tcgdx\TcgdxSet;
use App\Services\Tcgdx\TcgdxImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TcgdxImportCommand extends Command
{
    protected $signature = 'tcgdx:import 
                            {--set= : Import only one set by tcgdex id}
                            {--fresh : Truncate tcgdx tables before import}
                            {--cards-only : Import only cards for existing sets, skip set import}';

    protected $description = 'Import Pokemon sets and cards from TCGdex API';

    public function handle(TcgdxImportService $service): int
    {
        // Start pipeline tracking
        $pipelineRun = PipelineRun::start('tcgdx:import');

        $this->info('🎴 TCGdex Import');
        $this->newLine();

        // Fresh mode: DELETE FROMs
        if ($this->option('fresh')) {
            $this->warn('⚠️  Fresh mode: truncating tables...');
            
            if (!$this->confirm('This will delete all TCGdex data. Continue?')) {
                $this->info('Cancelled.');
                return self::SUCCESS;
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            TcgdxCard::truncate();
            TcgdxSet::truncate();
            TcgdxImportRun::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $this->info('✅ Tables truncated');
            $this->newLine();
        }

        // Import single set or all
        $setId = $this->option('set');
        $cardsOnly = $this->option('cards-only');
        
        if ($setId) {
            $this->info("📦 Importing set: {$setId}");
            $this->newLine();
            
            try {
                $result = $service->importSet($setId, function($message) {
                    $this->line($message);
                });
                
                $this->newLine();
                $this->info("✅ Set imported successfully!");
                $this->line("   Cards: {$result['cards_imported']}");
                
                return self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error("❌ Failed: {$e->getMessage()}");
                return self::FAILURE;
            }
        }

        // Import only cards for existing sets
        if ($cardsOnly) {
            $this->info('🎴 Importing cards only (sets already exist)');
            $this->newLine();
            
            try {
                $result = $service->runImportCardsOnly(function($message) {
                    $this->line($message);
                }, $pipelineRun);
                
                $this->newLine();
                $this->info('✅ Cards import completed!');
                $this->line("   Total Cards: {$result['cards_total']}");
                
                $pipelineRun->markSuccess([
                    'rows_created' => $result['cards_total'],
                ]);
                
                return self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error("❌ Failed: {$e->getMessage()}");
                $pipelineRun->markFailed($e->getMessage());
                return self::FAILURE;
            }
        }

        // Import all sets
        $run = $service->runImportAll(function($message) {
            $this->line($message);
        }, $pipelineRun);

        $this->newLine();
        
        if ($run->status === 'success') {
            $stats = $run->stats;
            $this->info('✅ Import completed successfully!');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Sets Imported', $stats['sets_imported'] ?? 0],
                    ['Sets Failed', $stats['sets_failed'] ?? 0],
                    ['Total Cards', $stats['cards_total'] ?? 0],
                ]
            );
            
            if (!empty($stats['failed_sets'])) {
                $this->warn('Failed sets:');
                foreach ($stats['failed_sets'] as $failed) {
                    $this->line("  - {$failed['set_id']}: {$failed['error']}");
                }
            }
            
            // Mark pipeline run as success
            $pipelineRun->markSuccess([
                'rows_processed' => $stats['sets_imported'] ?? 0,
                'rows_created' => $stats['cards_total'] ?? 0,
                'errors_count' => $stats['sets_failed'] ?? 0,
            ]);
            
            return self::SUCCESS;
        }

        $this->error('❌ Import failed');
        $this->line("Error: {$run->error_message}");
        
        // Mark pipeline run as failed
        $pipelineRun->markFailed($run->error_message ?? 'Import failed');
        
        return self::FAILURE;
    }
}
