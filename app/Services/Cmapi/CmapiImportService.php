<?php

namespace App\Services\Cmapi;

use App\Models\Cmapi\CmapiCard;
use App\Models\Cmapi\CmapiCardPriceSnapshot;
use App\Models\Cmapi\CmapiImportRun;
use App\Models\Cmapi\CmapiSet;
use Throwable;

class CmapiImportService
{
    protected CmapiClient $client;
    protected string $game;
    
    public function __construct(string $game = 'lorcana')
    {
        $this->game = $game;
        $this->client = new CmapiClient($game);
    }

    /**
     * Import only cards for existing sets
     */
    public function runImportCardsOnly(?callable $output = null, $pipelineRun = null): array
    {
        $allSets = CmapiSet::all();
        $cardsTotal = 0;
        
        if ($output) {
            $output("🎴 Importing cards for {$allSets->count()} existing sets...\n\n");
        }
        
        foreach ($allSets as $index => $set) {
            $progress = $index + 1;
            $total = $allSets->count();
            
            if ($output) {
                $output("[$progress/$total] Importing cards for set: {$set->name} (ID: {$set->cmapi_id})...\n");
            }

            try {
                $result = $this->importCardsForSet($set, $output);
                $cardsTotal += $result['cards_imported'] ?? 0;
                
                if ($output) {
                    $output("  ✅ {$result['cards_imported']} cards imported\n\n");
                }
                
                if ($pipelineRun && $progress % 5 === 0) {
                    $pipelineRun->updateStats([
                        'rows_created' => $cardsTotal,
                    ]);
                }
            } catch (Throwable $e) {
                if ($output) {
                    $output("  ❌ Failed importing cards: {$e->getMessage()}\n\n");
                }
            }
        }
        
        return [
            'cards_total' => $cardsTotal,
        ];
    }

    /**
     * Import all sets and their cards
     */
    public function runImportAll(?callable $output = null, $pipelineRun = null): CmapiImportRun
    {
        $run = CmapiImportRun::start($this->game, 'all', [
            'sets_total' => 0,
            'sets_imported' => 0,
            'sets_failed' => 0,
            'cards_total' => 0,
            'failed_sets' => [],
        ]);

        try {
            if ($output) {
                $output("🚀 Fetching {$this->game} sets from CardMarket API...\n");
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
            $importedSetIds = [];

            // Phase 1: Import all sets
            if ($output) {
                $output("📦 Phase 1: Importing sets...\n\n");
            }

            foreach ($sets as $index => $setData) {
                $setId = $setData['id'] ?? null;
                
                if (!$setId) {
                    continue;
                }

                $progress = $index + 1;
                
                if ($output) {
                    $output("[$progress/$totalSets] Importing set: {$setData['name']} (ID: {$setId})...\n");
                }

                try {
                    $normalizedSet = $this->client->normalizeSet($setData);
                    CmapiSet::updateOrCreate(
                        ['cmapi_id' => $normalizedSet['cmapi_id']],
                        $normalizedSet
                    );
                    $setsImported++;
                    $importedSetIds[] = $setId;
                    if ($output) {
                        $output("  ✅ Set imported\n\n");
                    }
                } catch (Throwable $e) {
                    $setsFailed++;
                    $failedSets[] = [
                        'set_id' => $setId,
                        'error' => $e->getMessage(),
                    ];
                    if ($output) {
                        $output("  ❌ Failed: {$e->getMessage()}\n\n");
                    }
                }
            }

            // Phase 2: Import cards for successfully imported sets
            if ($output) {
                $output("\n🎴 Phase 2: Importing cards...\n\n");
            }

            $importedSets = CmapiSet::whereIn('cmapi_id', $importedSetIds)->get();
            foreach ($importedSets as $index => $set) {
                $progress = $index + 1;
                $total = $importedSets->count();
                
                if ($output) {
                    $output("[$progress/$total] Importing cards for set: {$set->name}...\n");
                }

                try {
                    $result = $this->importCardsForSet($set, $output);
                    $cardsTotal += $result['cards_imported'] ?? 0;
                    
                    if ($output) {
                        $output("  ✅ {$result['cards_imported']} cards imported\n\n");
                    }
                    
                    if ($pipelineRun && $progress % 5 === 0) {
                        $pipelineRun->updateStats([
                            'rows_processed' => $setsImported,
                            'rows_created' => $cardsTotal,
                            'errors_count' => $setsFailed,
                        ]);
                    }
                } catch (Throwable $e) {
                    if ($output) {
                        $output("  ❌ Failed importing cards: {$e->getMessage()}\n\n");
                    }
                }
            }

            // Determine success/failure
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
     */
    public function importSet(string $setId, ?callable $output = null): array
    {
        $setData = $this->client->getSet($setId);
        
        if (!$setData) {
            throw new \Exception("Set not found: {$setId}");
        }

        $normalizedSet = $this->client->normalizeSet($setData);
        
        $set = CmapiSet::updateOrCreate(
            ['cmapi_id' => $normalizedSet['cmapi_id']],
            $normalizedSet
        );

        $result = $this->importCardsForSet($set, $output);
        
        return [
            'set_id' => $set->id,
            'cards_imported' => $result['cards_imported'],
        ];
    }

    /**
     * Import cards for a specific set
     */
    public function importCardsForSet(CmapiSet $set, ?callable $output = null): array
    {
        if (!$set->id) {
            throw new \Exception("Set {$set->cmapi_id} does not have a database ID");
        }
        
        $cards = $this->client->listCardsBySet($set->cmapi_id);
        $cardsImported = 0;

        foreach ($cards as $cardData) {
            $cardId = $cardData['id'] ?? null;
            
            if (!$cardId) {
                continue;
            }

            try {
                $normalizedCard = $this->client->normalizeCard($cardData, $set->id);
                
                $card = CmapiCard::updateOrCreate(
                    ['cmapi_id' => $normalizedCard['cmapi_id']],
                    $normalizedCard
                );
                
                // Save price snapshot for historical tracking
                $this->savePriceSnapshot($card, $cardData);
                
                $cardsImported++;
            } catch (Throwable $e) {
                if ($output) {
                    $output("  ⚠️  Skipping card {$cardId}: {$e->getMessage()}\n");
                }
            }
        }

        return [
            'cards_imported' => $cardsImported,
        ];
    }

    /**
     * Save price snapshot with language-specific pricing if available
     */
    protected function savePriceSnapshot(CmapiCard $card, array $cardData): void
    {
        $now = now();
        $snapshots = [];

        // Extract CardMarket prices if available
        if (isset($cardData['prices']['cardmarket'])) {
            $prices = $cardData['prices']['cardmarket'];
            
            // Default price (no language specified)
            if (isset($prices['lowest_near_mint']) && is_numeric($prices['lowest_near_mint']) && $prices['lowest_near_mint'] !== null) {
                $snapshots[] = [
                    'cmapi_card_id' => $card->id,
                    'price_eur' => round((float)$prices['lowest_near_mint'], 2), // Already in EUR
                    'price_usd' => null,
                    'language' => null,
                    'condition' => 'NM',
                    'recorded_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Language-specific prices (e.g., lowest_near_mint_DE, lowest_near_mint_FR)
            foreach ($prices as $key => $value) {
                if (preg_match('/lowest_near_mint_([A-Z]{2})/', $key, $matches)) {
                    $language = strtolower($matches[1]);
                    if (is_numeric($value) && $value !== null) {
                        $snapshots[] = [
                            'cmapi_card_id' => $card->id,
                            'price_eur' => round((float)$value, 2), // Already in EUR
                            'price_usd' => null,
                            'language' => $language,
                            'condition' => 'NM',
                            'recorded_at' => $now,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }
        }
        
        // Fallback: TCGPlayer USD price if CardMarket not available
        if (empty($snapshots) && isset($cardData['prices']['tcg_player']['market_price'])) {
            $usdPrice = $cardData['prices']['tcg_player']['market_price'];
            if (is_numeric($usdPrice) && $usdPrice !== null) {
                $snapshots[] = [
                    'cmapi_card_id' => $card->id,
                    'price_eur' => null,
                    'price_usd' => round((float)$usdPrice, 2), // Already in USD
                    'language' => 'en', // TCGPlayer is US-based
                    'condition' => 'NM',
                    'recorded_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Bulk insert snapshots if any
        if (count($snapshots) > 0) {
            CmapiCardPriceSnapshot::insert($snapshots);
        }
    }
}
