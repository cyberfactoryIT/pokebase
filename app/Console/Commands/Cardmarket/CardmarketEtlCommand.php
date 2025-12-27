<?php

namespace App\Console\Commands\Cardmarket;

use App\Jobs\DownloadCardmarketFilesJob;
use App\Models\CardmarketImportRun;
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
        $runUuid = (string) Str::uuid();
        $startTime = microtime(true);

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
            
            $catalogueResult = $this->downloader->downloadCatalogue($runUuid, $forceDownload);
            $priceGuideResult = $this->downloader->downloadPriceGuide($runUuid, $forceDownload);

            if (!$catalogueResult['success']) {
                throw new \Exception("Catalogue download failed: {$catalogueResult['message']}");
            }

            if (!$priceGuideResult['success']) {
                throw new \Exception("Price guide download failed: {$priceGuideResult['message']}");
            }

            $this->info('✅ Downloads complete');
            $this->newLine();

            // Store versions in run metadata
            $run->update([
                'source_catalogue_version' => $catalogueResult['version'],
                'source_priceguide_version' => $priceGuideResult['version'],
                'meta' => [
                    'catalogue_path' => $catalogueResult['path'],
                    'priceguide_path' => $priceGuideResult['path'],
                ],
            ]);

            // Step 2: Import catalogue
            $this->info('📦 STEP 2: Importing product catalogue...');
            $this->line('─────────────────────────────────');
            
            $catalogueImportResult = $this->importer->importCatalogue($catalogueResult['path'], $run);

            if (!$catalogueImportResult['success']) {
                throw new \Exception("Catalogue import failed: {$catalogueImportResult['message']}");
            }

            $this->info("✅ Catalogue: {$catalogueImportResult['rows_upserted']} products imported");
            $this->newLine();

            // Step 3: Import price guide
            $this->info('💰 STEP 3: Importing price guide...');
            $this->line('─────────────────────────────────');
            
            $priceGuideImportResult = $this->importer->importPriceGuide($priceGuideResult['path'], $run, $asOfDate);

            if (!$priceGuideImportResult['success']) {
                throw new \Exception("Price guide import failed: {$priceGuideImportResult['message']}");
            }

            $this->info("✅ Prices: {$priceGuideImportResult['rows_upserted']} quotes imported");
            if (isset($priceGuideImportResult['as_of_date'])) {
                $this->line("   Snapshot date: {$priceGuideImportResult['as_of_date']}");
            }
            $this->newLine();

            // Mark success
            $run->markSuccess();

            // Summary
            $duration = round(microtime(true) - $startTime, 2);
            
            $this->info('🎉 ETL PIPELINE COMPLETE!');
            $this->line('═════════════════════════');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Run UUID', $runUuid],
                    ['Duration', "{$duration} seconds"],
                    ['Products Imported', number_format($catalogueImportResult['rows_upserted'])],
                    ['Prices Imported', number_format($priceGuideImportResult['rows_upserted'])],
                    ['Total Rows Read', number_format($catalogueImportResult['rows_read'] + $priceGuideImportResult['rows_read'])],
                    ['Status', '✅ SUCCESS'],
                ]
            );

            return self::SUCCESS;

        } catch (\Exception $e) {
            $run->markFailed($e->getMessage());
            
            $this->newLine();
            $this->error('❌ ETL pipeline failed!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            $this->line('Check logs for details: ' . config('cardmarket.logging.channel'));

            return self::FAILURE;
        }
    }
}
