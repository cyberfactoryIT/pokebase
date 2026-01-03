<?php

namespace App\Console\Commands\Cardmarket;

use App\Jobs\DownloadCardmarketFilesJob;
use App\Models\CardmarketImportRun;
use App\Models\PipelineRun;
use App\Services\Cardmarket\CardmarketDownloader;
use App\Services\Cardmarket\CardmarketImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CardmarketEtlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cardmarket:etl 
                            {--as-of= : Date for price snapshot (YYYY-MM-DD), defaults to today}
                            {--queue : Run import via queue jobs}
                            {--force-download : Force download even if cached}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run full Cardmarket ETL pipeline: download -> import catalogue -> import prices';

    protected CardmarketDownloader $downloader;
    protected CardmarketImporter $importer;

    /**
     * Create a new command instance.
     */
    public function __construct(CardmarketDownloader $downloader, CardmarketImporter $importer)
    {
        parent::__construct();
        $this->downloader = $downloader;
        $this->importer = $importer;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $asOfDate = $this->option('as-of');
        $useQueue = $this->option('queue');
        $forceDownload = $this->option('force-download');

        $this->info('🚀 Starting Cardmarket ETL Pipeline');
        $this->info('==================================');
        $this->newLine();

        // If using queue, dispatch orchestrator job
        if ($useQueue) {
            DownloadCardmarketFilesJob::dispatch($asOfDate, $forceDownload)
                ->onQueue(config('cardmarket.queue.name'));
            
            $this->info('✅ ETL pipeline dispatched to queue: ' . config('cardmarket.queue.name'));
            return self::SUCCESS;
        }

        // Synchronous ETL
        return $this->runSync($asOfDate, $forceDownload);
    }

    /**
     * Run ETL synchronously.
     */
    protected function runSync(?string $asOfDate, bool $forceDownload): int
    {
        // Increase memory limit for large JSON files
        ini_set('memory_limit', '512M');
        
        $runUuid = (string) Str::uuid();
        $startTime = microtime(true);

        // Start pipeline tracking
        $pipelineRun = PipelineRun::start('cardmarket:etl');

        // Create import run
        $run = CardmarketImportRun::create([
            'run_uuid' => $runUuid,
            'type' => 'full',
            'status' => 'running',
        ]);

        try {
            // Step 1: Download files
            $this->info('📥 STEP 1: Downloading files...');
            $this->line('─────────────────────────────────');
            
            // Download products and prices for Pokemon
            $productsResult = $this->downloader->downloadProducts('pokemon', $forceDownload);
            $pricesResult = $this->downloader->downloadPrices('pokemon', $forceDownload);
            
            if (!$productsResult['success']) {
                throw new \Exception("Products download failed: {$productsResult['message']}");
            }
            
            if (!$pricesResult['success']) {
                throw new \Exception("Prices download failed: {$pricesResult['message']}");
            }
            
            $this->info('✅ Downloads complete');
            $this->line("   Products: {$productsResult['path']}");
            $this->line("   Prices: {$pricesResult['path']}");
            $this->newLine();

            // Store in run metadata
            $run->update([
                'meta' => [
                    'products_path' => $productsResult['path'],
                    'prices_path' => $pricesResult['path'],
                    'products_version' => $productsResult['version'] ?? null,
                    'prices_version' => $pricesResult['version'] ?? null,
                ],
            ]);

            // Step 2: Import products
            $this->info('📦 STEP 2: Importing product catalogue...');
            $this->line('─────────────────────────────────');
            
            $productsImportResult = $this->importer->importProducts($productsResult['path'], $run);
            
            if (!$productsImportResult['success']) {
                throw new \Exception("Products import failed: {$productsImportResult['message']}");
            }
            
            $this->info("✅ Catalogue: {$productsImportResult['rows_upserted']} products imported");
            $this->newLine();

            // Step 3: Import prices
            $this->info('💰 STEP 3: Importing price guide...');
            $this->line('─────────────────────────────────');
            
            $pricesImportResult = $this->importer->importPrices($pricesResult['path'], $run, $asOfDate);
            
            if (!$pricesImportResult['success']) {
                throw new \Exception("Prices import failed: {$pricesImportResult['message']}");
            }

            $this->info("✅ Prices: {$pricesImportResult['rows_upserted']} quotes imported");
            if ($asOfDate) {
                $this->line("   Snapshot date: {$asOfDate}");
            }
            $this->newLine();

            // Mark success
            $run->markSuccess();

            // Mark pipeline run as success
            $totalProcessed = $productsImportResult['rows_read'] + $pricesImportResult['rows_read'];
            $totalImported = $productsImportResult['rows_upserted'] + $pricesImportResult['rows_upserted'];
            
            $pipelineRun->markSuccess([
                'rows_processed' => $totalProcessed,
                'rows_created' => $totalImported,
            ]);

            // Summary
            $duration = round(microtime(true) - $startTime, 2);
            
            $this->info('🎉 ETL PIPELINE COMPLETE!');
            $this->line('═════════════════════════');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Run UUID', $runUuid],
                    ['Duration', "{$duration} seconds"],
                    ['Products Imported', number_format($productsImportResult['rows_upserted'])],
                    ['Prices Imported', number_format($pricesImportResult['rows_upserted'])],
                    ['Total Rows Read', number_format($totalProcessed)],
                    ['Status', '✅ SUCCESS'],
                ]
            );

            return self::SUCCESS;

        } catch (\Exception $e) {
            $run->markFailed($e->getMessage());
            $pipelineRun->markFailed($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            $this->newLine();
            $this->error('❌ ETL pipeline failed!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            $this->line('Check logs for details: ' . config('cardmarket.logging.channel'));

            return self::FAILURE;
        }
    }
}
