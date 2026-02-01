<?php

namespace App\Services\Cmapi;

use App\Models\Cmapi\CmapiCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CardMarketPriceSyncService
{
    protected array $gameIdMap = [
        'lorcana' => 19,
        'onepiece' => 26,
    ];

    /**
     * Download and import products and prices from CardMarket S3
     */
    public function importFromS3(string $game): array
    {
        $gameId = $this->gameIdMap[$game] ?? null;
        
        if (!$gameId) {
            throw new \Exception("Unsupported game: {$game}");
        }

        $stats = [
            'products_imported' => 0,
            'prices_imported' => 0,
            'errors' => 0,
        ];

        // Step 1: Download and import products
        $productsUrl = "https://downloads.s3.cardmarket.com/productCatalog/productList/products_singles_{$gameId}.json";
        $productsData = $this->downloadJson($productsUrl);
        
        if ($productsData && isset($productsData['products'])) {
            $stats['products_imported'] = $this->importProducts($game, $productsData['products']);
        } else {
            Log::error("Failed to download products from {$productsUrl}");
            $stats['errors']++;
        }

        // Step 2: Download and import prices
        $pricesUrl = "https://downloads.s3.cardmarket.com/productCatalog/priceGuide/price_guide_{$gameId}.json";
        $pricesData = $this->downloadJson($pricesUrl);
        
        if ($pricesData && isset($pricesData['priceGuides'])) {
            $stats['prices_imported'] = $this->importPrices($game, $pricesData['priceGuides']);
        } else {
            Log::error("Failed to download prices from {$pricesUrl}");
            $stats['errors']++;
        }

        return $stats;
    }

    /**
     * Download JSON from URL
     */
    protected function downloadJson(string $url): ?array
    {
        try {
            Log::info("Downloading from: {$url}");
            
            $response = Http::timeout(120)->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                Log::info("Downloaded " . count($data) . " items from {$url}");
                return $data;
            }

            Log::error("Failed to download from {$url}: " . $response->status());
            return null;
        } catch (\Exception $e) {
            Log::error("Exception downloading {$url}: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Import products to staging
     */
    protected function importProducts(string $game, array $products): int
    {
        $imported = 0;
        $batchSize = 500;
        $batch = [];

        foreach ($products as $product) {
            // Skip non-array items (metadata, etc)
            if (!is_array($product)) {
                continue;
            }
            
            // We don't filter by categoryId here - just import all products from singles file

            $batch[] = [
                'cardmarket_id' => (string)$product['idProduct'],
                'game' => $game,
                'name' => $product['name'] ?? 'Unknown',
                'set_name' => $product['expansionName'] ?? $product['categoryName'] ?? null,
                'number' => $product['number'] ?? null,
                'rarity' => $product['rarity'] ?? null,
                'language' => null, // Products file doesn't have language
                'raw_data' => json_encode($product),
                'fetched_at' => now(),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= $batchSize) {
                $this->insertOrUpdateProducts($batch);
                $imported += count($batch);
                $batch = [];
            }
        }

        // Insert remaining
        if (count($batch) > 0) {
            $this->insertOrUpdateProducts($batch);
            $imported += count($batch);
        }

        return $imported;
    }

    /**
     * Bulk insert/update products
     */
    protected function insertOrUpdateProducts(array $batch): void
    {
        foreach ($batch as $product) {
            DB::table('staging_cmapi_products')->updateOrInsert(
                ['cardmarket_id' => $product['cardmarket_id']],
                $product
            );
        }
    }

    /**
     * Import prices to staging
     */
    protected function importPrices(string $game, array $priceGuide): int
    {
        $imported = 0;
        $batchSize = 1000;
        $batch = [];

        foreach ($priceGuide as $priceData) {
            $cardmarketId = (string)$priceData['idProduct'];
            
            // Find staging product
            $stagingProduct = DB::table('staging_cmapi_products')
                ->where('cardmarket_id', $cardmarketId)
                ->where('game', $game)
                ->first();

            if (!$stagingProduct) {
                continue; // Skip if product not in staging
            }

            // CardMarket S3 price structure (flat, no language/condition breakdown):
            // {
            //   "idProduct": 726997,
            //   "avg": 294.51,      // Average price (cents)
            //   "low": 799,         // Lowest price (cents)
            //   "trend": 572.52,    // Trend price (cents)
            //   "avg1": 290.00,     // 1-day average
            //   "avg7": 285.00,     // 7-day average
            //   "avg30": 280.00,    // 30-day average
            //   "avg-foil": null,   // Foil prices (if applicable)
            //   "low-foil": null,
            //   "trend-foil": 0
            // }

            $now = now();
            
            // Build comprehensive prices JSON structure
            $prices = [
                'regular' => [
                    'low' => isset($priceData['low']) && is_numeric($priceData['low']) && $priceData['low'] > 0 
                        ? round($priceData['low'] / 100, 2) : null,
                    'avg' => isset($priceData['avg']) && is_numeric($priceData['avg']) && $priceData['avg'] > 0 
                        ? round($priceData['avg'] / 100, 2) : null,
                    'trend' => isset($priceData['trend']) && is_numeric($priceData['trend']) && $priceData['trend'] > 0 
                        ? round($priceData['trend'] / 100, 2) : null,
                    'avg1' => isset($priceData['avg1']) && is_numeric($priceData['avg1']) && $priceData['avg1'] > 0 
                        ? round($priceData['avg1'] / 100, 2) : null,
                    'avg7' => isset($priceData['avg7']) && is_numeric($priceData['avg7']) && $priceData['avg7'] > 0 
                        ? round($priceData['avg7'] / 100, 2) : null,
                    'avg30' => isset($priceData['avg30']) && is_numeric($priceData['avg30']) && $priceData['avg30'] > 0 
                        ? round($priceData['avg30'] / 100, 2) : null,
                ],
                'foil' => [
                    'low' => isset($priceData['low-foil']) && is_numeric($priceData['low-foil']) && $priceData['low-foil'] > 0 
                        ? round($priceData['low-foil'] / 100, 2) : null,
                    'avg' => isset($priceData['avg-foil']) && is_numeric($priceData['avg-foil']) && $priceData['avg-foil'] > 0 
                        ? round($priceData['avg-foil'] / 100, 2) : null,
                    'trend' => isset($priceData['trend-foil']) && is_numeric($priceData['trend-foil']) && $priceData['trend-foil'] > 0 
                        ? round($priceData['trend-foil'] / 100, 2) : null,
                    'avg1' => isset($priceData['avg1-foil']) && is_numeric($priceData['avg1-foil']) && $priceData['avg1-foil'] > 0 
                        ? round($priceData['avg1-foil'] / 100, 2) : null,
                    'avg7' => isset($priceData['avg7-foil']) && is_numeric($priceData['avg7-foil']) && $priceData['avg7-foil'] > 0 
                        ? round($priceData['avg7-foil'] / 100, 2) : null,
                    'avg30' => isset($priceData['avg30-foil']) && is_numeric($priceData['avg30-foil']) && $priceData['avg30-foil'] > 0 
                        ? round($priceData['avg30-foil'] / 100, 2) : null,
                ],
            ];
            
            // Use trend as primary price_eur (fallback to avg, then low, then 0)
            $priceEur = $prices['regular']['trend'] 
                ?? $prices['regular']['avg'] 
                ?? $prices['regular']['low'] 
                ?? 0;
            $priceTrendEur = $prices['regular']['trend'] ?? 0;
            
            // Create single record per product with all prices in JSON
            $batch[] = [
                'staging_product_id' => $stagingProduct->id,
                'cardmarket_id' => $cardmarketId,
                'language' => 'en',
                'condition' => 'NM',
                'price_eur' => $priceEur,
                'prices' => json_encode($prices),
                'price_trend_eur' => $priceTrendEur,
                'available_items' => null,
                'price_date' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $imported++;

            // Batch insert
            if (count($batch) >= $batchSize) {
                DB::table('staging_cmapi_prices')->insert($batch);
                $batch = [];
            }
        }

        // Insert remaining
        if (count($batch) > 0) {
            DB::table('staging_cmapi_prices')->insert($batch);
        }

        return $imported;
    }

    /**
     * Move validated data from staging to production with history
     */
    public function promoteToProduction(string $game): array
    {
        $stats = [
            'promoted' => 0,
            'errors' => 0,
        ];

        $stagingProducts = DB::table('staging_cmapi_products')
            ->where('game', $game)
            ->where('status', 'pending')
            ->get();

        foreach ($stagingProducts as $product) {
            try {
                DB::transaction(function () use ($product) {
                    // Find matching card by cardmarket_id first
                    $card = CmapiCard::where('cardmarket_id', $product->cardmarket_id)->first();
                    
                    if (!$card) {
                        // CardMarket doesn't have proper set/number, try matching by name
                        $card = CmapiCard::where('game', $product->game)
                            ->where('name', $product->name)
                            ->first();
                    }
                    
                    if (!$card) {
                        throw new \Exception("Card not found for cardmarket_id: {$product->cardmarket_id}, name: {$product->name}");
                    }

                    // Update card's cardmarket_id if not set
                    if (!$card->cardmarket_id) {
                        $card->update(['cardmarket_id' => $product->cardmarket_id]);
                    }

                    // Get staging prices (now just one row with all prices in JSON)
                    $stagingPrice = DB::table('staging_cmapi_prices')
                        ->where('staging_product_id', $product->id)
                        ->first();

                    if (!$stagingPrice || !$stagingPrice->price_eur) {
                        throw new \Exception("No prices found for product {$product->id}");
                    }

                    $today = now()->toDateString();
                    $priceDate = $stagingPrice->price_date ?? $today;

                    // Insert into price history (single row with all prices in JSON)
                    DB::table('cmapi_price_history')->updateOrInsert(
                        [
                            'cmapi_card_id' => $card->id,
                            'price_date' => $priceDate,
                        ],
                        [
                            'cardmarket_id' => $product->cardmarket_id,
                            'language' => $stagingPrice->language,
                            'condition' => $stagingPrice->condition,
                            'price_eur' => $stagingPrice->price_eur,
                            'prices' => $stagingPrice->prices,
                            'price_trend_eur' => $stagingPrice->price_trend_eur,
                            'available_items' => $stagingPrice->available_items,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );

                    // Update card with latest trend price
                    $card->update(['price_eur' => $stagingPrice->price_eur]);

                    // Mark as validated
                    DB::table('staging_cmapi_products')
                        ->where('id', $product->id)
                        ->update(['status' => 'validated', 'updated_at' => now()]);
                });

                $stats['promoted']++;
            } catch (\Exception $e) {
                $stats['errors']++;
                
                DB::table('staging_cmapi_products')
                    ->where('id', $product->id)
                    ->update([
                        'status' => 'error',
                        'error_message' => $e->getMessage(),
                        'updated_at' => now(),
                    ]);
                
                Log::error("Failed to promote product {$product->id}: {$e->getMessage()}");
            }
        }

        return $stats;
    }

    /**
     * Clean old staging data (keep last 7 days)
     */
    public function cleanOldStaging(): int
    {
        $cutoff = now()->subDays(7);
        
        return DB::table('staging_cmapi_products')
            ->where('status', 'validated')
            ->where('updated_at', '<', $cutoff)
            ->delete();
    }
}
