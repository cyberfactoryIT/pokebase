<?php

namespace App\Console\Commands\Cardmarket;

use App\Jobs\ImportCardmarketCatalogueJob;
use App\Jobs\ImportCardmarketPriceGuideJob;
use App\Models\CardmarketImportRun;
use App\Services\Cardmarket\CardmarketImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CardmarketImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cardmarket:import 
                            {game? : Game to import (pokemon, mtg, yugioh). Defaults to config default_game}
                            {--products : Import only products}
                            {--prices : Import only prices}
                            {--as-of= : Date for price snapshot (YYYY-MM-DD), defaults to today}
                            {--from-local= : Import from local file path instead of downloading}
                            {--queue : Run import via queue jobs}
                            {--dry-run : Parse and report counts without writing to DB}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Cardmarket product and price data for a game';

    protected CardmarketImporter $importer;

    /**
     * Create a new command instance.
     */
    public function __construct(CardmarketImporter $importer)
    {
        parent::__construct();
        $this->importer = $importer;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $game = $this->argument('game') ?? config('cardmarket.default_game');
        $importProducts = $this->option('products');
        $importPrices = $this->option('prices');
        $asOfDate = $this->option('as-of');
        $fromLocal = $this->option('from-local');
        $useQueue = $this->option('queue');
        $dryRun = $this->option('dry-run');

        // Validate game
        if (!config("cardmarket.games.{$game}")) {
            $this->error("Invalid game: {$game}");
            $this->info("Available games: " . implode(', ', array_keys(config('cardmarket.games'))));
            return self::FAILURE;
        }

        // If no specific option, import both
        if (!$importProducts && !$importPrices) {
            $importProducts = true;
            $importPrices = true;
        }

        // Validate local file if provided
        if ($fromLocal && !file_exists($fromLocal)) {
            $this->error("Local file not found: {$fromLocal}");
            return self::FAILURE;
        }

        $gameName = config("cardmarket.games.{$game}.name");
        $this->info("Starting Cardmarket import for {$gameName}...");
        $this->newLine();

        // If using queue
        if ($useQueue) {
            return $this->handleQueued($game, $importProducts, $importPrices, $asOfDate, $fromLocal);
        }

        // Synchronous import
        return $this->handleSync($game, $importProducts, $importPrices, $asOfDate, $fromLocal, $dryRun);
    }

    /**
     * Handle synchronous import.
     */
    protected function handleSync(string $game, bool $importProducts, bool $importPrices, ?string $asOfDate, ?string $fromLocal, bool $dryRun): int
    {
        $allSuccessful = true;

        // Create import run
        $type = $importProducts && $importPrices ? 'full' : ($importProducts ? 'products' : 'prices');
        $run = CardmarketImportRun::create([
            'run_uuid' => (string) Str::uuid(),
            'type' => $type,
            'status' => 'running',
        ]);

        try {
            // Import products
            if ($importProducts) {
                $this->info('📦 Importing products...');
                
                $jsonPath = $fromLocal ?: $this->findLatestProductsFile($game);
                
                if (!$jsonPath) {
                    $this->error('No products file found. Run cardmarket:download first.');
                    $allSuccessful = false;
                } else {
                    $result = $this->importer->importProducts($jsonPath, $run, $dryRun);
                    
                    if ($result['success']) {
                        $this->info("✅ {$result['message']}");
                        $this->line("   Rows read: {$result['rows_read']}");
                        $this->line("   Rows upserted: {$result['rows_upserted']}");
                    } else {
                        $this->error("❌ {$result['message']}");
                        $allSuccessful = false;
                    }
                }
                
                $this->newLine();
            }

            // Import prices
            if ($importPrices) {
                $this->info('💰 Importing prices...');
                
                $jsonPath = $fromLocal ?: $this->findLatestPricesFile($game);
                
                if (!$jsonPath) {
                    $this->error('No prices file found. Run cardmarket:download first.');
                    $allSuccessful = false;
                } else {
                    $result = $this->importer->importPrices($jsonPath, $run, $asOfDate, $dryRun);
                    
                    if ($result['success']) {
                        $this->info("✅ {$result['message']}");
                        $this->line("   Rows read: {$result['rows_read']}");
                        $this->line("   Rows upserted: {$result['rows_upserted']}");
                        if (isset($result['as_of_date'])) {
                            $this->line("   Snapshot date: {$result['as_of_date']}");
                        }
                    } else {
                        $this->error("❌ {$result['message']}");
                        $allSuccessful = false;
                    }
                }
                
                $this->newLine();
            }

            // Mark run as complete
            if (!$dryRun) {
                if ($allSuccessful) {
                    $run->markSuccess();
                    $this->info('🎉 Import completed successfully!');
                } else {
                    $run->markFailed('One or more imports failed');
                    $this->error('⚠️  Some imports failed. Check the logs for details.');
                }
            }

            return $allSuccessful ? self::SUCCESS : self::FAILURE;

        } catch (\Exception $e) {
            if (!$dryRun) {
                $run->markFailed($e->getMessage());
            }
            
            $this->error('Import failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Handle queued import.
     */
    protected function handleQueued(bool $importCatalogue, bool $importPriceGuide, ?string $asOfDate, ?string $fromLocal): int
    {
        $jobsDispatched = 0;

        if ($importCatalogue) {
            $csvPath = $fromLocal ?: $this->findLatestCatalogueFile();
            
            if ($csvPath) {
                ImportCardmarketCatalogueJob::dispatch($csvPath)
                    ->onQueue(config('cardmarket.queue.name'));
                $this->info('✅ Catalogue import job dispatched');
                $jobsDispatched++;
            } else {
                $this->error('No catalogue file found');
            }
        }

        if ($importPriceGuide) {
            $csvPath = $fromLocal ?: $this->findLatestPriceGuideFile();
            
            if ($csvPath) {
                ImportCardmarketPriceGuideJob::dispatch($csvPath, $asOfDate)
                    ->onQueue(config('cardmarket.queue.name'));
                $this->info('✅ Price guide import job dispatched');
                $jobsDispatched++;
            } else {
                $this->error('No price guide file found');
            }
        }

        if ($jobsDispatched > 0) {
            $this->newLine();
            $this->info("🎉 {$jobsDispatched} job(s) dispatched to queue: " . config('cardmarket.queue.name'));
            return self::SUCCESS;
        }

        return self::FAILURE;
    }

    /**
     * Find the latest products JSON file.
     */
    protected function findLatestProductsFile(string $game): ?string
    {
        // Try both storage/app and storage/app/private (Laravel 11+)
        $paths = [
            storage_path('app/' . config('cardmarket.storage.raw')),
            storage_path('app/private/' . config('cardmarket.storage.raw')),
        ];
        
        foreach ($paths as $rawPath) {
            if (!is_dir($rawPath)) {
                continue;
            }

            $files = glob($rawPath . "/{$game}_products_*.json");
            
            if (!empty($files)) {
                // Sort by modification time, newest first
                usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
                return $files[0];
            }
        }
        
        return null;
    }

    /**
     * Find the latest prices JSON file.
     */
    protected function findLatestPricesFile(string $game): ?string
    {
        // Try both storage/app and storage/app/private (Laravel 11+)
        $paths = [
            storage_path('app/' . config('cardmarket.storage.raw')),
            storage_path('app/private/' . config('cardmarket.storage.raw')),
        ];
        
        foreach ($paths as $rawPath) {
            if (!is_dir($rawPath)) {
                continue;
            }

            $files = glob($rawPath . "/{$game}_prices_*.json");
            
            if (!empty($files)) {
                // Sort by modification time, newest first
                usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
                return $files[0];
            }
        }
        
        return null;
    }
}
