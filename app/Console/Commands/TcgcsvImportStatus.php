<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TcgcsvImportLog;

class TcgcsvImportStatus extends Command
{
    protected $signature = 'tcgcsv:import-status 
                            {--batch-id= : Show specific batch details}
                            {--last : Show last import}
                            {--limit=10 : Number of imports to show}';
    
    protected $description = 'Show TCGCSV import history and status';

    public function handle(): int
    {
        $batchId = $this->option('batch-id');
        $last = $this->option('last');
        $limit = (int) $this->option('limit');
        
        if ($batchId) {
            return $this->showBatchDetails($batchId);
        }
        
        if ($last) {
            $log = TcgcsvImportLog::latest('started_at')->first();
            if (!$log) {
                $this->warn('No imports found');
                return self::SUCCESS;
            }
            // Use batch_id for imports, run_id for checkups
            $id = $log->batch_id ?? $log->run_id;
            return $this->showBatchDetails($id);
        }
        
        return $this->showImportHistory($limit);
    }
    
    protected function showImportHistory(int $limit): int
    {
        $logs = TcgcsvImportLog::orderBy('started_at', 'desc')
            ->limit($limit)
            ->get();
        
        if ($logs->isEmpty()) {
            $this->warn('No imports found');
            return self::SUCCESS;
        }
        
        $this->info('TCGCSV Import History');
        $this->newLine();
        
        $rows = [];
        foreach ($logs as $log) {
            $statusColor = match($log->status) {
                'completed', 'ok' => 'green',
                'failed' => 'red',
                'warn' => 'yellow',
                'in_progress' => 'yellow',
                default => 'gray',
            };
            
            // Different display for checkup vs import
            if ($log->type === 'checkup') {
                $rows[] = [
                    $log->run_id,
                    'checkup',
                    "<fg={$statusColor}>{$log->status}</>",
                    $log->started_at->format('Y-m-d H:i:s'),
                    $log->duration_ms ? "{$log->duration_ms}ms" : 'N/A',
                    $log->message ?? 'N/A',
                ];
            } else {
                $rows[] = [
                    $log->batch_id ?? 'N/A',
                    'import',
                    "<fg={$statusColor}>{$log->status}</>",
                    $log->started_at->format('Y-m-d H:i:s'),
                    $log->duration ?? 'N/A',
                    "G:{$log->groups_processed} P:{$log->products_processed} Pr:{$log->prices_processed}",
                ];
            }
        }
        
        $this->table(
            ['ID', 'Type', 'Status', 'Started', 'Duration', 'Details'],
            $rows
        );
        
        $this->newLine();
        $this->line('Use --batch-id to see details: <fg=cyan>php artisan tcgcsv:import-status --batch-id=BATCH_ID</>');
        $this->line('Use --last to see last import: <fg=cyan>php artisan tcgcsv:import-status --last</>');
        
        return self::SUCCESS;
    }
    
    protected function showBatchDetails(?string $batchId): int
    {
        if (!$batchId) {
            $this->error("No batch ID or run ID provided");
            return self::FAILURE;
        }
        
        $log = TcgcsvImportLog::where('batch_id', $batchId)
            ->orWhere('run_id', $batchId)
            ->first();
        
        if (!$log) {
            $this->error("Import not found: {$batchId}");
            return self::FAILURE;
        }
        
        $this->info('TCGCSV Import Details');
        $this->newLine();
        
        // General info
        $statusColor = match($log->status) {
            'completed' => 'green',
            'failed' => 'red',
            'in_progress' => 'yellow',
            default => 'gray',
        };
        
        $this->table(
            ['Field', 'Value'],
            [
                ['Batch ID', $log->batch_id],
                ['Status', "<fg={$statusColor}>{$log->status}</>"],
                ['Started', $log->started_at->format('Y-m-d H:i:s')],
                ['Completed', $log->completed_at ? $log->completed_at->format('Y-m-d H:i:s') : 'N/A'],
                ['Duration', $log->duration ?? 'N/A'],
            ]
        );
        
        $this->newLine();
        
        // Options
        if ($log->options) {
            $this->line('<fg=bright-cyan>Options:</>');
            $this->table(
                ['Option', 'Value'],
                collect($log->options)->map(fn($v, $k) => [$k, $v ?? 'null'])->toArray()
            );
            $this->newLine();
        }
        
        // Statistics
        $this->line('<fg=bright-cyan>Groups:</>');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Processed', $log->groups_processed],
                ['New', "<fg=green>{$log->groups_new}</>"],
                ['Updated', "<fg=yellow>{$log->groups_updated}</>"],
                ['Failed', $log->groups_failed > 0 ? "<fg=red>{$log->groups_failed}</>" : '0'],
            ]
        );
        $this->newLine();
        
        $this->line('<fg=bright-cyan>Products:</>');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Processed', $log->products_processed],
                ['New', "<fg=green>{$log->products_new}</>"],
                ['Updated', "<fg=yellow>{$log->products_updated}</>"],
                ['Failed', $log->products_failed > 0 ? "<fg=red>{$log->products_failed}</>" : '0'],
            ]
        );
        $this->newLine();
        
        $this->line('<fg=bright-cyan>Prices:</>');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Processed', $log->prices_processed],
                ['New', "<fg=green>{$log->prices_new}</>"],
                ['Updated', "<fg=yellow>{$log->prices_updated}</>"],
                ['Failed', $log->prices_failed > 0 ? "<fg=red>{$log->prices_failed}</>" : '0'],
            ]
        );
        $this->newLine();
        
        // Completed groups
        if ($log->groups_completed && count($log->groups_completed) > 0) {
            $this->line('<fg=bright-cyan>Groups Completed:</> ' . count($log->groups_completed) . ' groups');
            if (count($log->groups_completed) <= 20) {
                $this->line(implode(', ', $log->groups_completed));
            } else {
                $this->line(implode(', ', array_slice($log->groups_completed, 0, 20)) . '... and ' . (count($log->groups_completed) - 20) . ' more');
            }
            $this->newLine();
        }
        
        // Errors
        if ($log->error_details && count($log->error_details) > 0) {
            $this->line('<fg=red>Errors:</>');
            foreach ($log->error_details as $groupId => $error) {
                $this->line("  Group {$groupId}: {$error}");
            }
            $this->newLine();
        }
        
        return self::SUCCESS;
    }
}
