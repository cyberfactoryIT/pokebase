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
        $products = $this->downloadJson($productsUrl);
        
        if ($products) {
            $stats['products_imported'] = $this->importProducts($game, $products);
        } else {
            Log::error("Failed to download products from {$productsUrl}");
            $stats['errors']++;
        }

        // Step 2: Download and import prices
        $pricesUrl = "https://downloads.s3.cardmarket.com/productCatalog/priceGuide/price_guide_{$gameId}.json";
        $prices = $this->downloadJson($pricesUrl);
        
        if ($prices) {
            $stats['prices_imported'] = $this->importPrices($game, $prices);
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
            // Skip non-single products (sealed, etc)
            if (isset($product['categoryId']) && $product['categoryId'] != 1) {
                continue;
            }

            $batch[] = [
                'cardmarket_id' => (string)$product['idProduct'],
                'game' => $game,
                'name' => $product['enName'] ?? $product['locName'] ?? 'Unknown',
                'set_name' => $product['expansionName'] ?? null,
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

            // Clear old prices for this product
            if ($imported === 0) {
                DB::table('staging_cmapi_prices')
                    ->where('staging_product_id', $stagingProduct->id)
                    ->delete();
            }

            // Parse price data structure
            // Format: {"1": {"NM": {"avg": 1.5, "lowPrice": 1.2, "trendPrice": 1.4}}}
            // Where "1" is language ID (1=English, 2=French, 3=German, 4=Spanish, 5=Italian)
            $languageMap = [
                '1' => 'en',
                '2' => 'fr', 
                '3' => 'de',
                '4' => 'es',
                '5' => 'it',
            ];

            foreach ($priceData as $langId => $conditions) {
                if (!is_array($conditions) || !isset($languageMap[$langId])) {
                    continue;
                }

                $language = $languageMap[$langId];

                foreach ($conditions as $condition => $prices) {
                    if (!is_array($prices)) {
                        continue;
                    }

                    $batch[] = [
                        'staging_product_id' => $stagingProduct->id,
                        'cardmarket_id' => $cardmarketId,
                        'language' => $language,
                        'condition' => $condition,
                        'price_eur' => $prices['lowPrice'] ?? $prices['avg'] ?? null,
                        'price_trend_eur' => $prices['trendPrice'] ?? null,
                        'available_items' => null, // Not in price guide
                        'price_date' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (count($batch) >= $batchSize) {
                        DB::table('staging_cmapi_prices')->insert($batch);
                        $imported += count($batch);
                        $batch = [];
                    }
                }
            }
        }

        // Insert remaining
        if (count($batch) > 0) {
            DB::table('staging_cmapi_prices')->insert($batch);
            $imported += count($batch);
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
                    // Find matching card by cardmarket_id OR by set+number
                    $card = CmapiCard::where('cardmarket_id', $product->cardmarket_id)->first();
                    
                    if (!$card) {
                        // Try matching by set name + card number
                        $card = CmapiCard::whereHas('set', function($q) use ($product) {
                            $q->where('name', 'LIKE', '%' . $product->set_name . '%');
                        })
                        ->where('number', $product->number)
                        ->where('game', $product->game)
                        ->first();
                    }
                    
                    if (!$card) {
                        throw new \Exception("Card not found for cardmarket_id: {$product->cardmarket_id}, set: {$product->set_name}, number: {$product->number}");
                    }

                    // Update card's cardmarket_id if not set
                    if (!$card->cardmarket_id) {
                        $card->update(['cardmarket_id' => $product->cardmarket_id]);
                    }

                    // Get staging prices
                    $stagingPrices = DB::table('staging_cmapi_prices')
                        ->where('staging_product_id', $product->id)
                        ->get();

                    if ($stagingPrices->isEmpty()) {
                        throw new \Exception("No prices found for product {$product->id}");
                    }

                    $today = now()->toDateString();

                    foreach ($stagingPrices as $price) {
                        if (!$price->price_eur) {
                            continue;
                        }

                        // Insert into price history (upsert for today)
                        DB::table('cmapi_price_history')->updateOrInsert(
                            [
                                'cmapi_card_id' => $card->id,
                                'language' => $price->language,
                                'condition' => $price->condition,
                                'price_date' => $today,
                            ],
                            [
                                'cardmarket_id' => $product->cardmarket_id,
                                'price_eur' => $price->price_eur,
                                'price_trend_eur' => $price->price_trend_eur,
                                'available_items' => $price->available_items,
                                'updated_at' => now(),
                                'created_at' => now(),
                            ]
                        );
                    }

                    // Update card with latest NM price in English (language=en)
                    $defaultPrice = $stagingPrices->where('language', 'en')
                        ->where('condition', 'NM')
                        ->first();

                    if ($defaultPrice && $defaultPrice->price_eur) {
                        $card->update(['price_eur' => $defaultPrice->price_eur]);
                    }

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
