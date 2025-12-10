<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PokemonImportLog;

class PokemonImportStatus extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pokemon:import-status 
                            {--batch-id= : Show details for specific batch ID}
                            {--last : Show only the last import}
                            {--limit=10 : Number of imports to show}';

    /**
     * The console command description.
     */
    protected $description = 'View Pokemon import status and history';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $batchId = $this->option('batch-id');
        $showLast = $this->option('last');
        $limit = (int) $this->option('limit');

        if ($batchId) {
            return $this->showBatchDetails($batchId);
        }

        if ($showLast) {
            $import = PokemonImportLog::latest('started_at')->first();
            if ($import) {
                return $this->showBatchDetails($import->batch_id);
            } else {
                $this->error('No imports found');
                return static::FAILURE;
            }
        }

        return $this->showImportList($limit);
    }

    protected function showImportList(int $limit): int
    {
        $imports = PokemonImportLog::orderBy('started_at', 'desc')
            ->limit($limit)
            ->get();

        if ($imports->isEmpty()) {
            $this->info('No imports found');
            return static::SUCCESS;
        }

        $this->info('Recent Pokemon Card Imports:');
        $this->newLine();

        $data = $imports->map(function ($import) {
            $statusEmoji = [
                'started' => '🔵',
                'in_progress' => '🟡',
                'completed' => '✅',
                'failed' => '❌',
                'cancelled' => '⚫',
            ];

            return [
                $statusEmoji[$import->status] ?? '⚪',
                $import->batch_id,
                $import->status,
                $import->started_at->format('Y-m-d H:i:s'),
                $import->completed_at?->format('Y-m-d H:i:s') ?? 'In Progress',
                $import->duration ?? 'N/A',
                $import->cards_processed,
                $import->cards_new,
                $import->cards_updated,
                $import->cards_failed,
                $import->progress_percentage ? $import->progress_percentage . '%' : 'N/A',
            ];
        });

        $this->table(
            ['', 'Batch ID', 'Status', 'Started', 'Completed', 'Duration', 'Processed', 'New', 'Updated', 'Failed', 'Progress'],
            $data
        );

        $this->newLine();
        $this->info('Use --batch-id=<ID> to see detailed information about a specific import');

        return static::SUCCESS;
    }

    protected function showBatchDetails(string $batchId): int
    {
        $import = PokemonImportLog::where('batch_id', $batchId)->first();

        if (!$import) {
            $this->error("Import with batch ID '{$batchId}' not found");
            return static::FAILURE;
        }

        $this->info("Import Details - {$batchId}");
        $this->newLine();

        $this->table(
            ['Field', 'Value'],
            [
                ['Batch ID', $import->batch_id],
                ['Status', $import->status],
                ['Set Code', $import->set_code ?? 'All Sets'],
                ['Started At', $import->started_at->format('Y-m-d H:i:s')],
                ['Completed At', $import->completed_at?->format('Y-m-d H:i:s') ?? 'Not completed'],
                ['Duration', $import->duration ?? 'N/A'],
                ['Start Page', $import->start_page],
                ['Current Page', $import->current_page ?? 'N/A'],
                ['Total Pages', $import->total_pages ?? 'N/A'],
                ['Progress', $import->progress_percentage ? $import->progress_percentage . '%' : 'N/A'],
                ['Cards Processed', $import->cards_processed],
                ['Cards New', $import->cards_new],
                ['Cards Updated', $import->cards_updated],
                ['Cards Failed', $import->cards_failed],
            ]
        );

        if ($import->pages_completed && count($import->pages_completed) > 0) {
            $this->newLine();
            $this->info('Pages Completed: ' . implode(', ', $import->pages_completed));
        }

        if ($import->error_message) {
            $this->newLine();
            $this->error('Error Message:');
            $this->line($import->error_message);
        }

        if ($import->failed_cards && count($import->failed_cards) > 0) {
            $this->newLine();
            $this->warn('Failed Cards (' . count($import->failed_cards) . '):');
            
            $failedData = collect($import->failed_cards)->map(function ($card) {
                return [
                    $card['name'] ?? 'Unknown',
                    $card['id'] ?? 'Unknown',
                    substr($card['error'] ?? 'Unknown error', 0, 50),
                ];
            })->toArray();

            $this->table(
                ['Name', 'ID', 'Error'],
                $failedData
            );
        }

        return static::SUCCESS;
    }
}
