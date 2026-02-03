<?php

namespace App\Console\Commands\Cardmarket;

use App\Models\CardmarketImportRunLorcana;
use App\Services\Cardmarket\CardmarketImporterLorcana;
use Illuminate\Console\Command;

class CardmarketImportLorcanaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cardmarket:import-lorcana 
                            {--products : Import only products}
                            {--prices : Import only prices}
                            {--as-of= : Date for price snapshot (YYYY-MM-DD), defaults to today}
                            {--from-local= : Import from local file path instead of downloading}
                            {--dry-run : Parse and report counts without writing to DB}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Lorcana product and price data from CardMarket S3';

    protected CardmarketImporterLorcana $importer;

    /**
     * Create a new command instance.
     */
    public function __construct(CardmarketImporterLorcana $importer)
    {
        parent::__construct();
        $this->importer = $importer;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $importProducts = $this->option('products');
        $importPrices = $this->option('prices');
        $asOfDate = $this->option('as-of');
        $fromLocal = $this->option('from-local');
        $dryRun = $this->option('dry-run');

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

        $this->info("Starting CardMarket import for Lorcana...");
        $this->newLine();

        return $this->handleSync($importProducts, $importPrices, $asOfDate, $fromLocal, $dryRun);
    }

    /**
     * Handle synchronous import.
     */
    protected function handleSync(bool $importProducts, bool $importPrices, ?string $asOfDate, ?string $fromLocal, bool $dryRun): int
    {
        $allSuccessful = true;

        // Create import run
        $type = $importProducts && $importPrices ? 'full' : ($importProducts ? 'products' : 'prices');
        $run = CardmarketImportRunLorcana::create([
            'type' => $type,
            'status' => 'running',
        ]);

        try {
            // Import products
            if ($importProducts) {
                $this->info('📦 Importing Lorcana products...');
                
                $jsonPath = $fromLocal ?: $this->findLatestProductsFile();
                
                if (!$jsonPath) {
                    $this->error('No products file found. Run cardmarket:download lorcana first.');
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
                $this->info('💰 Importing Lorcana prices...');
                
                $jsonPath = $fromLocal ?: $this->findLatestPricesFile();
                
                if (!$jsonPath) {
                    $this->error('No prices file found. Run cardmarket:download lorcana first.');
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
                    $run->markCompleted();
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
     * Find the latest products JSON file.
     */
    protected function findLatestProductsFile(): ?string
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

            $files = glob($rawPath . "/lorcana_products_*.json");
            
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
    protected function findLatestPricesFile(): ?string
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

            $files = glob($rawPath . "/lorcana_prices_*.json");
            
            if (!empty($files)) {
                // Sort by modification time, newest first
                usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
                return $files[0];
            }
        }
        
        return null;
    }
}
