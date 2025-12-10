<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PokemonImportService;

class ImportAllPokemonCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan pokemon:import-all
     */
    protected $signature = 'pokemon:import-all
                            {--from-page=1 : Start from specific page}
                            {--set= : Import only specific set code}
                            {--force : Force reimport of existing cards}';

    /**
     * The console command description.
     */
    protected $description = 'Import all Pokémon TCG cards from the Pokemon TCG API into card_catalog';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(PokemonImportService $importService): int
    {
        $fromPage = (int) $this->option('from-page');
        $setCode = $this->option('set');
        $force = $this->option('force');

        $this->info('Starting full Pokémon TCG import...');
        
        if ($fromPage > 1) {
            $this->info("Resuming from page {$fromPage}");
        }
        
        if ($setCode) {
            $this->info("Importing only set: {$setCode}");
        }

        try {
            $stats = $importService->importAllCards(
                function (string $message) {
                    $this->line($message);
                },
                $fromPage,
                $setCode,
                $force
            );
            
            $this->newLine();
            $this->info('Import Statistics:');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Batch ID', $stats['batch_id'] ?? 'N/A'],
                    ['Total Cards Processed', $stats['processed']],
                    ['New Cards', $stats['new']],
                    ['Updated Cards', $stats['updated']],
                    ['Failed Cards', $stats['failed']],
                    ['Duration', $stats['duration']],
                ]
            );
            
            $this->newLine();
            $this->info('View detailed logs with: php artisan pokemon:import-status --batch-id=' . ($stats['batch_id'] ?? ''));
            
        } catch (\Throwable $e) {
            $this->error('Error during import: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return static::FAILURE;
        }

        $this->info('Pokémon TCG import finished successfully.');
        return static::SUCCESS;
    }
}
