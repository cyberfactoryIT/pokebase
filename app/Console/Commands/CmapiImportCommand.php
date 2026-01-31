<?php

namespace App\Console\Commands;

use App\Models\Cmapi\CmapiCard;
use App\Models\Cmapi\CmapiImportRun;
use App\Models\Cmapi\CmapiSet;
use App\Models\PipelineRun;
use App\Services\Cmapi\CmapiImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CmapiImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmapi:import 
                            {--game=lorcana : Game to import (lorcana, onepiece)}
                            {--episode= : Import only one episode/set by ID}
                            {--fresh : Truncate CMAPI tables before import}
                            {--cards-only : Import only cards for existing sets, skip set import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Lorcana/One Piece sets and cards from CardMarket API (via RapidAPI)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $game = $this->option('game');
        $pipelineRun = PipelineRun::start("cmapi:import --game={$game}");

        $this->info("🎴 CardMarket API Import - {$game}");
        $this->newLine();

        if ($this->option('fresh')) {
            $this->warn('⚠️  Fresh mode: truncating CMAPI tables...');
            
            if (!$this->confirm('This will delete all CardMarket API data. Continue?')) {
                $this->info('Cancelled.');
                return self::SUCCESS;
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            CmapiCard::truncate();
            CmapiSet::truncate();
            CmapiImportRun::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $this->info('✅ Tables truncated');
            $this->newLine();
        }

        $service = new CmapiImportService($game);
        $episodeId = $this->option('episode');
        $cardsOnly = $this->option('cards-only');
        
        // Import single episode
        if ($episodeId) {
            $this->info("📦 Importing episode: {$episodeId}");
            $this->newLine();
            
            try {
                $result = $service->importSet($episodeId, function($message) {
                    $this->line($message);
                });
                
                $this->newLine();
                $this->info("✅ Episode imported successfully!");
                $this->line("   Cards: {$result['cards_imported']}");
                
                $pipelineRun->markSuccess([
                    'rows_created' => $result['cards_imported'],
                ]);
                
                return self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error("❌ Failed: {$e->getMessage()}");
                $pipelineRun->markFailed($e->getMessage());
                return self::FAILURE;
            }
        }

        // Import cards only
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

        // Full import
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
                    ['Game', ucfirst($game)],
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
            
            $pipelineRun->markSuccess([
                'rows_processed' => $stats['sets_imported'] ?? 0,
                'rows_created' => $stats['cards_total'] ?? 0,
                'errors_count' => $stats['sets_failed'] ?? 0,
            ]);
            
            return self::SUCCESS;
        }

        $this->error('❌ Import failed');
        $this->line("Error: {$run->error_message}");
        
        $pipelineRun->markFailed($run->error_message ?? 'Import failed');
        
        return self::FAILURE;
    }
}
