<?php

namespace App\Console\Commands;

use App\Models\TcgcsvImportLog;
use App\Services\Tcgcsv\TcgcsvCheckupService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TcgcsvCheckup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tcgcsv:checkup
                            {--json : Output results as JSON}
                            {--fail-on-warn : Exit with non-zero code on warnings}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run data integrity checkup on TCGCSV data';

    /**
     * Execute the console command.
     */
    public function handle(TcgcsvCheckupService $checkupService): int
    {
        $startTime = microtime(true);
        $runId = Str::uuid()->toString();
        
        $this->info("Starting TCGCSV data integrity checkup...");
        $this->info("Run ID: {$runId}");
        $this->newLine();
        
        // Run the checkup
        $result = $checkupService->runCheckup();
        $status = $result['status'];
        $metrics = $result['metrics'];
        
        // Calculate duration
        $durationMs = (int) round((microtime(true) - $startTime) * 1000);
        
        // Generate message
        $message = $checkupService->generateMessage($status, $metrics);
        
        // Write to database
        $log = TcgcsvImportLog::create([
            'type' => 'checkup',
            'run_id' => $runId,
            'status' => $status,
            'message' => $message,
            'started_at' => now(),
            'completed_at' => now(),
            'duration_ms' => $durationMs,
            'metrics' => $metrics,
        ]);
        
        // Output results
        if ($this->option('json')) {
            // JSON output only
            $this->line(json_encode([
                'run_id' => $runId,
                'status' => $status,
                'message' => $message,
                'duration_ms' => $durationMs,
                'metrics' => $metrics,
            ], JSON_PRETTY_PRINT));
        } else {
            // Human-readable output
            $this->displayHumanReadable($status, $message, $metrics, $durationMs);
        }
        
        // Determine exit code
        $exitCode = 0;
        if ($status === 'fail') {
            $exitCode = 1;
        } elseif ($status === 'warn' && $this->option('fail-on-warn')) {
            $exitCode = 1;
        }
        
        if ($exitCode !== 0 && !$this->option('json')) {
            $this->newLine();
            $this->error("Checkup completed with issues (exit code: {$exitCode})");
        }
        
        return $exitCode;
    }
    
    /**
     * Display human-readable checkup results
     *
     * @param string $status
     * @param string $message
     * @param array $metrics
     * @param int $durationMs
     * @return void
     */
    protected function displayHumanReadable(string $status, string $message, array $metrics, int $durationMs): void
    {
        // Status header
        $this->newLine();
        match($status) {
            'ok' => $this->info("✓ Status: OK"),
            'warn' => $this->warn("⚠ Status: WARNING"),
            'fail' => $this->error("✗ Status: FAILED"),
        };
        
        $this->line("Message: {$message}");
        $this->line("Duration: {$durationMs}ms");
        $this->newLine();
        
        // Metrics table
        $this->line("Data Counts:");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Groups', number_format($metrics['groups_count'])],
                ['Products', number_format($metrics['products_count'])],
                ['Prices', number_format($metrics['prices_count'])],
            ]
        );
        
        // Integrity issues
        $this->newLine();
        $this->line("Referential Integrity:");
        $integrityRows = [
            ['Orphaned Prices', $metrics['prices_without_product_count'], $metrics['prices_without_product_count'] > 0 ? '✗' : '✓'],
            ['Orphaned Products', $metrics['products_without_group_count'], $metrics['products_without_group_count'] > 0 ? '✗' : '✓'],
        ];
        $this->table(['Check', 'Count', 'Status'], $integrityRows);
        
        // Parsing completeness
        $this->newLine();
        $this->line("Parsing Completeness (informational - non-card products may lack these):");
        $parsingRows = [
            ['Missing card_number', $metrics['products_missing_card_number_count'], '→'],
            ['Missing rarity', $metrics['products_missing_rarity_count'], '→'],
        ];
        $this->table(['Check', 'Count', 'Status'], $parsingRows);
        
        // Duplicates
        $this->newLine();
        $this->line("Duplicates:");
        $duplicateRows = [
            ['Duplicate Groups', $metrics['groups_duplicates'], $metrics['groups_duplicates'] > 0 ? '✗' : '✓'],
            ['Duplicate Products', $metrics['products_duplicates'], $metrics['products_duplicates'] > 0 ? '✗' : '✓'],
            ['Duplicate Prices', $metrics['prices_duplicates'], $metrics['prices_duplicates'] > 0 ? '✗' : '✓'],
        ];
        $this->table(['Check', 'Count', 'Status'], $duplicateRows);
    }
}
