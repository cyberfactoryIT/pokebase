<?php

namespace App\Services\Tcgdx;

use App\Models\Tcgdx\TcgdxCard;
use App\Models\Tcgdx\TcgdxImportRun;
use App\Models\Tcgdx\TcgdxSet;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * TCGdex Import Service
 * 
 * How to use:
 * - Import all: php artisan tcgdx:import
 * - Import one set: php artisan tcgdx:import --set=base1
 * - Fresh import: php artisan tcgdx:import --fresh
 * 
 * This service is idempotent and resumable:
 * - Sets are upserted by tcgdex_id
 * - Cards are upserted by tcgdex_id
 * - Failed sets are logged but don't stop the entire import
 * - Run is marked as success if <20% of sets fail
 */
class TcgdxImportService
{
    protected TcgdxClient $client;
    
    public function __construct(TcgdxClient $client)
    {
        $this->client = $client;
    }

    /**
     * Import all Pokemon sets and their cards
     * 
     * @param callable|null $output Progress callback
     * @param \App\Models\PipelineRun|null $pipelineRun Pipeline run to update
     * @return TcgdxImportRun
     */
    public function runImportAll(?callable $output = null, $pipelineRun = null): TcgdxImportRun
    {
        $run = TcgdxImportRun::create([
            'started_at' => now(),
            'status' => 'running',
            'scope' => 'all',
            'stats' => [
                'sets_total' => 0,
                'sets_imported' => 0,
                'sets_failed' => 0,
                'cards_total' => 0,
                'failed_sets' => [],
            ],
        ]);

        try {
            if ($output) {
                $output("🚀 Fetching sets from TCGdex...\n");
            }

            $sets = $this->client->listSets();
            $totalSets = count($sets);
            
            $run->addStats(['sets_total' => $totalSets]);

            if ($output) {
                $output("📦 Found {$totalSets} sets\n\n");
            }

            $setsImported = 0;
            $setsFailed = 0;
            $cardsTotal = 0;
            $failedSets = [];

            foreach ($sets as $index => $setData) {
                $setId = $setData['id'] ?? null;
                
                if (!$setId) {
                    continue;
                }

                $progress = $index + 1;
                
                if ($output) {
                    $output("[$progress/$totalSets] Importing set: {$setId}...\n");
                }

                try {
                    $result = $this->importSet($setId, $output);
                    $setsImported++;
                    $cardsTotal += $result['cards_imported'] ?? 0;
                    
                    if ($output) {
                        $output("  ✅ {$result['cards_imported']} cards imported\n\n");
                    }
                    
                    // Update pipeline stats every 20 sets
                    if ($pipelineRun && $progress % 20 === 0) {
                        $pipelineRun->updateStats([
                            'rows_processed' => $setsImported,
                            'rows_created' => $cardsTotal,
                            'errors_count' => $setsFailed,
                        ]);
                    }
                } catch (Throwable $e) {
                    $setsFailed++;
                    $failedSets[] = [
                        'set_id' => $setId,
                        'error' => $e->getMessage(),
                    ];
                    
                    // Ensure any pending transaction is closed
                    if (DB::transactionLevel() > 0) {
                        DB::rollBack();
                    }
                    
                    if ($output) {
                        $output("  ❌ Failed: {$e->getMessage()}\n\n");
                    }
                }
            }

            // Determine success/failure based on 20% threshold
            $failureRate = $totalSets > 0 ? ($setsFailed / $totalSets) : 0;
            $isSuccess = $failureRate < 0.20;

            $stats = [
                'sets_total' => $totalSets,
                'sets_imported' => $setsImported,
                'sets_failed' => $setsFailed,
                'cards_total' => $cardsTotal,
                'failed_sets' => $failedSets,
            ];

            if ($isSuccess) {
                $run->markAsSuccess($stats);
                if ($output) {
                    $output("✅ Import completed successfully!\n");
                    $output("   Sets: {$setsImported}/{$totalSets}\n");
                    $output("   Cards: {$cardsTotal}\n");
                }
            } else {
                $run->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'stats' => $stats,
                    'error_message' => "Too many sets failed: {$setsFailed}/{$totalSets}",
                ]);
                if ($output) {
                    $output("❌ Import failed: too many sets failed ({$setsFailed}/{$totalSets})\n");
                }
            }

        } catch (Throwable $e) {
            $run->markAsFailed($e->getMessage());
            
            if ($output) {
                $output("❌ Import failed: {$e->getMessage()}\n");
            }
        }

        return $run;
    }

    /**
     * Import a single set and its cards
     * 
     * @param string $setId TCGdex set identifier
     * @param callable|null $output
     * @return array Stats
     */
    public function importSet(string $setId, ?callable $output = null): array
    {
        DB::beginTransaction();
        
        try {
            // Fetch set data
            $setData = $this->client->getSet($setId);
            
            if (!$setData) {
                throw new \Exception("Set not found: {$setId}");
            }

            // Normalize and upsert set
            $normalizedSet = $this->client->normalizeSet($setData);
            
            $set = TcgdxSet::updateOrCreate(
                ['tcgdex_id' => $normalizedSet['tcgdex_id']],
                $normalizedSet
            );

            // Import cards for this set (pass set object instead of setId string)
            $result = $this->importCardsForSet($set, $output);

            DB::commit();
            
            return [
                'set_id' => $set->id,
                'cards_imported' => $result['cards_imported'],
            ];

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Import cards for a specific set
     * 
     * @param TcgdxSet $set The set model instance
     * @param callable|null $output
     * @return array Stats
     */
    public function importCardsForSet(TcgdxSet $set, ?callable $output = null): array
    {
        // Fetch card summaries from set endpoint
        $cardSummaries = $this->client->listCardsBySet($set->tcgdex_id);
        $cardsImported = 0;

        foreach ($cardSummaries as $cardSummary) {
            $cardId = $cardSummary['id'] ?? null;
            
            if (!$cardId) {
                continue;
            }

            // Fetch full card details (HP, rarity, types, etc.)
            $cardData = $this->client->getCard($cardId);
            
            if (!$cardData) {
                if ($output) {
                    $output("  ⚠️  Skipping card {$cardId}: not found\n");
                }
                continue;
            }

            // Normalize and upsert card
            $normalizedCard = $this->client->normalizeCard($cardData, $set->id);
            
            TcgdxCard::updateOrCreate(
                ['tcgdex_id' => $normalizedCard['tcgdex_id']],
                $normalizedCard
            );
            
            $cardsImported++;
        }

        return [
            'cards_imported' => $cardsImported,
        ];
    }
}
