<?php

namespace App\Services\Cardmarket;

use App\Models\CardmarketImportRun;
use App\Models\CardmarketProduct;
use App\Models\CardmarketPriceQuote;
use App\Services\Cardmarket\Parsers\ProductCatalogueParser;
use App\Services\Cardmarket\Parsers\PriceGuideParser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CardmarketImporter
{
    protected string $logChannel;
    protected int $chunkSize;
    protected int $progressInterval;
    protected string $timezone;

    public function __construct()
    {
        $this->logChannel = config('cardmarket.logging.channel', 'cardmarket');
        $this->chunkSize = config('cardmarket.import.chunk_size', 2000);
        $this->progressInterval = config('cardmarket.import.progress_interval', 10);
        $this->timezone = config('cardmarket.import.timezone', 'Europe/Copenhagen');
    }

    /**
     * Import products from JSON.
     *
     * @param string $jsonPath Full filesystem path to JSON file
     * @param CardmarketImportRun $run
     * @param bool $dryRun If true, parse but don't write to DB
     * @return array ['success' => bool, 'rows_read' => int, 'rows_upserted' => int, 'message' => string]
     */
    public function importProducts(string $jsonPath, CardmarketImportRun $run, bool $dryRun = false): array
    {
        Log::channel($this->logChannel)->info('Starting products import', [
            'run_uuid' => $run->run_uuid,
            'json_path' => $jsonPath,
            'dry_run' => $dryRun,
        ]);

        try {
            $parser = new ProductCatalogueParser();
            $generator = $parser->parse($jsonPath);

            $rowsRead = 0;
            $rowsUpserted = 0;
            $batch = [];
            $chunkNumber = 0;

            foreach ($generator as $row) {
                $rowsRead++;
                $batch[] = $row;

                if (count($batch) >= $this->chunkSize) {
                    $chunkNumber++;
                    
                    if (!$dryRun) {
                        $upserted = $this->upsertProductsBatch($batch);
                        $rowsUpserted += $upserted;
                    } else {
                        $rowsUpserted += count($batch);
                    }

                    // Progress logging
                    if ($chunkNumber % $this->progressInterval === 0) {
                        Log::channel($this->logChannel)->info("Products progress: {$rowsRead} rows read, {$rowsUpserted} upserted");
                        
                        if (!$dryRun) {
                            $run->update([
                                'rows_read' => $rowsRead,
                                'rows_upserted' => $rowsUpserted,
                            ]);
                        }
                        
                        // Free memory
                        gc_collect_cycles();
                    }

                    $batch = [];
                }
            }

            // Process remaining rows
            if (!empty($batch)) {
                if (!$dryRun) {
                    $upserted = $this->upsertProductsBatch($batch);
                    $rowsUpserted += $upserted;
                } else {
                    $rowsUpserted += count($batch);
                }
            }

            // Final update
            if (!$dryRun) {
                $run->update([
                    'rows_read' => $rowsRead,
                    'rows_upserted' => $rowsUpserted,
                ]);
            }

            $message = $dryRun 
                ? "Dry run: Would import {$rowsRead} products"
                : "Successfully imported {$rowsUpserted} products";

            Log::channel($this->logChannel)->info($message, [
                'run_uuid' => $run->run_uuid,
                'rows_read' => $rowsRead,
                'rows_upserted' => $rowsUpserted,
            ]);

            return [
                'success' => true,
                'rows_read' => $rowsRead,
                'rows_upserted' => $rowsUpserted,
                'message' => $message,
            ];

        } catch (\Exception $e) {
            Log::channel($this->logChannel)->error('Products import failed', [
                'run_uuid' => $run->run_uuid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'rows_read' => $rowsRead ?? 0,
                'rows_upserted' => $rowsUpserted ?? 0,
                'message' => 'Import failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Import prices from JSON.
     *
     * @param string $jsonPath Full filesystem path to JSON file
     * @param CardmarketImportRun $run
     * @param string|null $asOfDate Date for this price snapshot (YYYY-MM-DD)
     * @param bool $dryRun If true, parse but don't write to DB
     * @return array ['success' => bool, 'rows_read' => int, 'rows_upserted' => int, 'message' => string]
     */
    public function importPrices(string $jsonPath, CardmarketImportRun $run, ?string $asOfDate = null, bool $dryRun = false): array
    {
        // Determine as_of_date
        $asOfDate = $asOfDate ? Carbon::parse($asOfDate, $this->timezone) : now($this->timezone);
        $asOfDateString = $asOfDate->toDateString();

        Log::channel($this->logChannel)->info('Starting prices import', [
            'run_uuid' => $run->run_uuid,
            'json_path' => $jsonPath,
            'as_of_date' => $asOfDateString,
            'dry_run' => $dryRun,
        ]);

        try {
            $parser = new PriceGuideParser();
            $generator = $parser->parse($jsonPath);

            $rowsRead = 0;
            $rowsUpserted = 0;
            $batch = [];
            $chunkNumber = 0;

            foreach ($generator as $row) {
                $rowsRead++;
                
                // Add as_of_date to row
                $row['as_of_date'] = $asOfDateString;
                
                $batch[] = $row;

                if (count($batch) >= $this->chunkSize) {
                    $chunkNumber++;
                    
                    if (!$dryRun) {
                        $upserted = $this->upsertPricesBatch($batch);
                        $rowsUpserted += $upserted;
                    } else {
                        $rowsUpserted += count($batch);
                    }

                    // Progress logging
                    if ($chunkNumber % $this->progressInterval === 0) {
                        Log::channel($this->logChannel)->info("Prices progress: {$rowsRead} rows read, {$rowsUpserted} upserted");
                        
                        if (!$dryRun) {
                            $run->update([
                                'rows_read' => $rowsRead,
                                'rows_upserted' => $rowsUpserted,
                            ]);
                        }
                        
                        // Free memory
                        gc_collect_cycles();
                    }

                    $batch = [];
                }
            }

            // Process remaining rows
            if (!empty($batch)) {
                if (!$dryRun) {
                    $upserted = $this->upsertPricesBatch($batch);
                    $rowsUpserted += $upserted;
                } else {
                    $rowsUpserted += count($batch);
                }
            }

            // Final update
            if (!$dryRun) {
                $run->update([
                    'rows_read' => $rowsRead,
                    'rows_upserted' => $rowsUpserted,
                ]);
            }

            $message = $dryRun 
                ? "Dry run: Would import {$rowsRead} price quotes for {$asOfDateString}"
                : "Successfully imported {$rowsUpserted} price quotes for {$asOfDateString}";

            Log::channel($this->logChannel)->info($message, [
                'run_uuid' => $run->run_uuid,
                'rows_read' => $rowsRead,
                'rows_upserted' => $rowsUpserted,
                'as_of_date' => $asOfDateString,
            ]);

            return [
                'success' => true,
                'rows_read' => $rowsRead,
                'rows_upserted' => $rowsUpserted,
                'message' => $message,
                'as_of_date' => $asOfDateString,
            ];

        } catch (\Exception $e) {
            Log::channel($this->logChannel)->error('Prices import failed', [
                'run_uuid' => $run->run_uuid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'rows_read' => $rowsRead ?? 0,
                'rows_upserted' => $rowsUpserted ?? 0,
                'message' => 'Import failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Upsert a batch of products.
     *
     * @param array $batch
     * @return int Number of rows affected
     */
    protected function upsertProductsBatch(array $batch): int
    {
        if (empty($batch)) {
            return 0;
        }

        // Add timestamps
        $now = now();
        foreach ($batch as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        try {
            DB::beginTransaction();

            // Use upsert for idempotency
            DB::table('cardmarket_products')->upsert(
                $batch,
                ['cardmarket_product_id'], // Unique key
                ['id_category', 'category_name', 'id_expansion', 'id_metacard', 'name', 'date_added', 'raw', 'updated_at'] // Update these fields
            );

            DB::commit();

            return count($batch);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::channel($this->logChannel)->error('Failed to upsert products batch', [
                'error' => $e->getMessage(),
                'batch_size' => count($batch),
            ]);

            throw $e;
        }
    }

    /**
     * Upsert a batch of price quotes.
     *
     * @param array $batch
     * @return int Number of rows affected
     */
    protected function upsertPricesBatch(array $batch): int
    {
        if (empty($batch)) {
            return 0;
        }

        // Add timestamps
        $now = now();
        foreach ($batch as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        try {
            DB::beginTransaction();

            // Use upsert for idempotency (one quote per product per date)
            DB::table('cardmarket_price_quotes')->upsert(
                $batch,
                ['cardmarket_product_id', 'as_of_date'], // Unique key
                ['id_category', 'currency', 'avg', 'low', 'trend', 'avg_holo', 'low_holo', 'trend_holo', 'avg1', 'avg7', 'avg30', 'raw', 'updated_at'] // Update these fields
            );

            DB::commit();

            return count($batch);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::channel($this->logChannel)->error('Failed to upsert prices batch', [
                'error' => $e->getMessage(),
                'batch_size' => count($batch),
            ]);

            throw $e;
        }
    }
}
