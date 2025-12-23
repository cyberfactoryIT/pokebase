<?php

namespace App\Services\Tcgcsv;

use App\Models\TcgcsvGroup;
use App\Models\TcgcsvProduct;
use App\Models\TcgcsvPrice;
use App\Models\TcgcsvImportLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TcgcsvImportService
{
    protected TcgcsvClient $client;
    protected int $categoryId;
    protected string $runId;
    protected ?TcgcsvImportLog $importLog = null;
    
    public function __construct(TcgcsvClient $client)
    {
        $this->client = $client;
        $this->categoryId = config('tcgcsv.category_id');
        $this->runId = 'tcgcsv_' . date('Ymd_His') . '_' . Str::random(6);
    }
    
    /**
     * Import all groups
     */
    public function importGroups(): array
    {
        Log::info("TCGCSV Import: Starting groups import", ['run_id' => $this->runId]);
        
        $response = $this->client->getGroups();
        
        // Handle different response formats
        $groupsData = $response['results'] ?? $response['data'] ?? $response;
        
        // If it's still not an array of arrays, wrap it
        if (!is_array($groupsData) || empty($groupsData)) {
            $groupsData = [];
        }
        
        $stats = [
            'total' => count($groupsData),
            'new' => 0,
            'updated' => 0,
            'failed' => 0,
        ];
        
        foreach ($groupsData as $groupData) {
            try {
                // Skip if not an array (API might return integers as keys)
                if (!is_array($groupData)) {
                    continue;
                }
                
                $this->upsertGroup($groupData, $stats);
            } catch (\Exception $e) {
                $stats['failed']++;
                Log::error("TCGCSV Import: Failed to import group", [
                    'run_id' => $this->runId,
                    'group_id' => is_array($groupData) ? ($groupData['groupId'] ?? null) : null,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        Log::info("TCGCSV Import: Groups import completed", array_merge(['run_id' => $this->runId], $stats));
        
        return $stats;
    }
    
    /**
     * Import products for a specific group
     */
    public function importProductsByGroup(int $groupId): array
    {
        Log::info("TCGCSV Import: Starting products import for group", [
            'run_id' => $this->runId,
            'group_id' => $groupId,
        ]);
        
        $response = $this->client->getProducts($groupId);
        
        // Handle different response formats
        $productsData = $response['results'] ?? $response['data'] ?? $response;
        
        if (!is_array($productsData) || empty($productsData)) {
            $productsData = [];
        }
        
        $stats = [
            'group_id' => $groupId,
            'total' => count($productsData),
            'new' => 0,
            'updated' => 0,
            'failed' => 0,
        ];
        
        foreach ($productsData as $productData) {
            // Skip if not an array
            if (!is_array($productData)) {
                continue;
            }
            
            try {
                $this->upsertProduct($groupId, $productData, $stats);
            } catch (\Exception $e) {
                $stats['failed']++;
                Log::error("TCGCSV Import: Failed to import product", [
                    'run_id' => $this->runId,
                    'group_id' => $groupId,
                    'product_id' => $productData['productId'] ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        Log::info("TCGCSV Import: Products import completed for group", array_merge(['run_id' => $this->runId], $stats));
        
        return $stats;
    }
    
    /**
     * Import prices for a specific group
     */
    public function importPricesByGroup(int $groupId): array
    {
        Log::info("TCGCSV Import: Starting prices import for group", [
            'run_id' => $this->runId,
            'group_id' => $groupId,
        ]);
        
        $response = $this->client->getPrices($groupId);
        
        // Handle different response formats
        $pricesData = $response['results'] ?? $response['data'] ?? $response;
        
        if (!is_array($pricesData) || empty($pricesData)) {
            $pricesData = [];
        }
        
        $stats = [
            'group_id' => $groupId,
            'total' => count($pricesData),
            'new' => 0,
            'updated' => 0,
            'failed' => 0,
        ];
        
        $snapshotAt = now();
        
        foreach ($pricesData as $priceData) {
            // Skip if not an array
            if (!is_array($priceData)) {
                continue;
            }
            
            try {
                $this->upsertPrice($groupId, $priceData, $snapshotAt, $stats);
            } catch (\Exception $e) {
                $stats['failed']++;
                Log::error("TCGCSV Import: Failed to import price", [
                    'run_id' => $this->runId,
                    'group_id' => $groupId,
                    'product_id' => $priceData['productId'] ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        Log::info("TCGCSV Import: Prices import completed for group", array_merge(['run_id' => $this->runId], $stats));
        
        return $stats;
    }
    
    /**
     * Import all groups and their products/prices
     */
    public function importAll(?int $specificGroupId = null, array $options = []): array
    {
        // Create import log entry
        $this->importLog = TcgcsvImportLog::create([
            'batch_id' => $this->runId,
            'status' => 'started',
            'started_at' => now(),
            'options' => $options,
        ]);
        
        Log::info("TCGCSV Import: Starting full import", [
            'run_id' => $this->runId,
            'specific_group' => $specificGroupId,
        ]);
        
        try {
            $overallStats = [
                'groups' => ['total' => 0, 'new' => 0, 'updated' => 0, 'failed' => 0],
                'products' => ['total' => 0, 'new' => 0, 'updated' => 0, 'failed' => 0],
                'prices' => ['total' => 0, 'new' => 0, 'updated' => 0, 'failed' => 0],
            ];
            
            $completedGroups = [];
            $errorsByGroup = [];
            
            // Import groups first (unless specific group is provided)
            if (!$specificGroupId) {
                $this->importLog->update(['status' => 'in_progress']);
                $groupStats = $this->importGroups();
                $overallStats['groups'] = $groupStats;
            }
            
            // Get groups to process
            $query = TcgcsvGroup::query();
            if ($specificGroupId) {
                $query->where('group_id', $specificGroupId);
            }
            
            $groups = $query->get();
            
            foreach ($groups as $group) {
                try {
                    // Import products
                    $productStats = $this->importProductsByGroup($group->group_id);
                    $overallStats['products']['total'] += $productStats['total'];
                    $overallStats['products']['new'] += $productStats['new'];
                    $overallStats['products']['updated'] += $productStats['updated'];
                    $overallStats['products']['failed'] += $productStats['failed'];
                    
                    // Import prices
                    $priceStats = $this->importPricesByGroup($group->group_id);
                    $overallStats['prices']['total'] += $priceStats['total'];
                    $overallStats['prices']['new'] += $priceStats['new'];
                    $overallStats['prices']['updated'] += $priceStats['updated'];
                    $overallStats['prices']['failed'] += $priceStats['failed'];
                    
                    $completedGroups[] = $group->group_id;
                    
                } catch (\Exception $e) {
                    $errorsByGroup[$group->group_id] = $e->getMessage();
                    
                    Log::error("TCGCSV Import: Failed to process group", [
                        'run_id' => $this->runId,
                        'group_id' => $group->group_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            // Update import log with final stats
            $this->importLog->update([
                'status' => 'completed',
                'completed_at' => now(),
                'groups_processed' => $overallStats['groups']['total'],
                'groups_new' => $overallStats['groups']['new'],
                'groups_updated' => $overallStats['groups']['updated'],
                'groups_failed' => $overallStats['groups']['failed'],
                'products_processed' => $overallStats['products']['total'],
                'products_new' => $overallStats['products']['new'],
                'products_updated' => $overallStats['products']['updated'],
                'products_failed' => $overallStats['products']['failed'],
                'prices_processed' => $overallStats['prices']['total'],
                'prices_new' => $overallStats['prices']['new'],
                'prices_updated' => $overallStats['prices']['updated'],
                'prices_failed' => $overallStats['prices']['failed'],
                'groups_completed' => $completedGroups,
                'error_details' => empty($errorsByGroup) ? null : $errorsByGroup,
            ]);
            
            Log::info("TCGCSV Import: Full import completed", array_merge(['run_id' => $this->runId], $overallStats));
            
            return $overallStats;
            
        } catch (\Exception $e) {
            // Mark import as failed
            $this->importLog->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_details' => ['general' => $e->getMessage()],
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Upsert a group record
     */
    protected function upsertGroup(array $data, array &$stats): void
    {
        $groupId = $data['groupId'] ?? null;
        
        if (!$groupId) {
            throw new \Exception('Missing groupId in group data');
        }
        
        $existing = TcgcsvGroup::where('group_id', $groupId)->exists();
        
        TcgcsvGroup::updateOrCreate(
            ['group_id' => $groupId],
            [
                'category_id' => $this->categoryId,
                'name' => $data['name'] ?? null,
                'abbreviation' => $data['abbreviation'] ?? null,
                'published_on' => isset($data['publishedOn']) ? $this->parseDate($data['publishedOn']) : null,
                'modified_on' => isset($data['modifiedOn']) ? $this->parseDate($data['modifiedOn']) : null,
                'raw' => $data,
            ]
        );
        
        if ($existing) {
            $stats['updated']++;
        } else {
            $stats['new']++;
        }
    }
    
    /**
     * Upsert a product record
     */
    protected function upsertProduct(int $groupId, array $data, array &$stats): void
    {
        $productId = $data['productId'] ?? null;
        
        if (!$productId) {
            throw new \Exception('Missing productId in product data');
        }
        
        $existing = TcgcsvProduct::where('product_id', $productId)->exists();
        
        // Parse extended data for card number and rarity
        $extendedData = $data['extendedData'] ?? [];
        $cardNumber = $this->extractFromExtendedData($extendedData, ['number', 'card number', 'collector number']);
        $rarity = $this->extractFromExtendedData($extendedData, ['rarity']);
        
        TcgcsvProduct::updateOrCreate(
            ['product_id' => $productId],
            [
                'category_id' => $this->categoryId,
                'group_id' => $groupId,
                'name' => $data['name'] ?? null,
                'clean_name' => $data['cleanName'] ?? null,
                'image_url' => $data['imageUrl'] ?? null,
                'rarity' => $rarity,
                'card_number' => $cardNumber,
                'modified_on' => isset($data['modifiedOn']) ? $this->parseDate($data['modifiedOn']) : null,
                'extended_data' => $extendedData,
                'raw' => $data,
            ]
        );
        
        if ($existing) {
            $stats['updated']++;
        } else {
            $stats['new']++;
        }
    }
    
    /**
     * Upsert a price record
     */
    protected function upsertPrice(int $groupId, array $data, $snapshotAt, array &$stats): void
    {
        $productId = $data['productId'] ?? null;
        
        if (!$productId) {
            throw new \Exception('Missing productId in price data');
        }
        
        // Extract printing and condition if present
        $printing = $data['subTypeName'] ?? $data['printing'] ?? 'Normal';
        $condition = $data['condition'] ?? null;
        
        $existing = TcgcsvPrice::where('product_id', $productId)
            ->where('printing', $printing)
            ->where('condition', $condition)
            ->where('snapshot_at', $snapshotAt)
            ->exists();
        
        TcgcsvPrice::updateOrCreate(
            [
                'product_id' => $productId,
                'printing' => $printing,
                'condition' => $condition,
                'snapshot_at' => $snapshotAt,
            ],
            [
                'category_id' => $this->categoryId,
                'group_id' => $groupId,
                'market_price' => $data['marketPrice'] ?? null,
                'low_price' => $data['lowPrice'] ?? null,
                'mid_price' => $data['midPrice'] ?? null,
                'high_price' => $data['highPrice'] ?? null,
                'direct_low_price' => $data['directLowPrice'] ?? null,
                'raw' => $data,
            ]
        );
        
        if ($existing) {
            $stats['updated']++;
        } else {
            $stats['new']++;
        }
    }
    
    /**
     * Extract value from extendedData array by matching field names
     */
    protected function extractFromExtendedData(array $extendedData, array $fieldNames): ?string
    {
        foreach ($extendedData as $item) {
            $name = strtolower($item['name'] ?? '');
            
            foreach ($fieldNames as $fieldName) {
                if ($name === strtolower($fieldName)) {
                    return $item['value'] ?? null;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Parse date string to Carbon instance
     */
    protected function parseDate(string $date): ?\DateTime
    {
        try {
            return new \DateTime($date);
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Get current run ID
     */
    public function getRunId(): string
    {
        return $this->runId;
    }
}
