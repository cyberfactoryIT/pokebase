<?php

namespace App\Services\Tcgcsv;

use Illuminate\Support\Facades\DB;

class TcgcsvCheckupService
{
    /**
     * Run comprehensive data integrity checkup
     * 
     * @return array ['status' => 'ok|warn|fail', 'metrics' => [...]]
     */
    public function runCheckup(): array
    {
        $metrics = [];
        
        // 1-3. Row counts
        $metrics['groups_count'] = DB::table('tcgcsv_groups')->count();
        $metrics['products_count'] = DB::table('tcgcsv_products')->count();
        $metrics['prices_count'] = DB::table('tcgcsv_prices')->count();
        
        // 4. Prices without product (orphaned prices)
        $metrics['prices_without_product_count'] = DB::table('tcgcsv_prices')
            ->leftJoin('tcgcsv_products', 'tcgcsv_prices.product_id', '=', 'tcgcsv_products.product_id')
            ->whereNull('tcgcsv_products.product_id')
            ->count();
        
        // 5. Products without group (orphaned products)
        $metrics['products_without_group_count'] = DB::table('tcgcsv_products')
            ->leftJoin('tcgcsv_groups', 'tcgcsv_products.group_id', '=', 'tcgcsv_groups.group_id')
            ->whereNull('tcgcsv_groups.group_id')
            ->count();
        
        // 6. Products missing card_number
        $metrics['products_missing_card_number_count'] = DB::table('tcgcsv_products')
            ->whereNull('card_number')
            ->orWhere('card_number', '')
            ->count();
        
        // 7. Products missing rarity
        $metrics['products_missing_rarity_count'] = DB::table('tcgcsv_products')
            ->whereNull('rarity')
            ->orWhere('rarity', '')
            ->count();
        
        // 8. Duplicate groups (by group_id)
        $metrics['groups_duplicates'] = DB::table('tcgcsv_groups')
            ->select('group_id')
            ->groupBy('group_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        
        // 9. Duplicate products (by product_id)
        $metrics['products_duplicates'] = DB::table('tcgcsv_products')
            ->select('product_id')
            ->groupBy('product_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        
        // 10. Duplicate prices (by product_id + printing + condition + snapshot_at)
        $metrics['prices_duplicates'] = DB::table('tcgcsv_prices')
            ->select('product_id', 'printing', 'condition', 'snapshot_at')
            ->groupBy('product_id', 'printing', 'condition', 'snapshot_at')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        
        // Determine status based on violations
        $status = $this->determineStatus($metrics);
        
        return [
            'status' => $status,
            'metrics' => $metrics,
        ];
    }
    
    /**
     * Determine checkup status based on metrics
     * 
     * @param array $metrics
     * @return string 'ok', 'warn', or 'fail'
     */
    protected function determineStatus(array $metrics): string
    {
        // FAIL conditions: any orphans or duplicates
        if ($metrics['prices_without_product_count'] > 0 ||
            $metrics['products_without_group_count'] > 0 ||
            $metrics['groups_duplicates'] > 0 ||
            $metrics['products_duplicates'] > 0 ||
            $metrics['prices_duplicates'] > 0) {
            return 'fail';
        }
        
        // Note: card_number and rarity can be missing for non-card products
        // (booster packs, ETBs, accessories), so these are tracked but not warnings
        
        // All clean
        return 'ok';
    }
    
    /**
     * Generate human-readable message based on status and metrics
     * 
     * @param string $status
     * @param array $metrics
     * @return string
     */
    public function generateMessage(string $status, array $metrics): string
    {
        if ($status === 'ok') {
            return 'All integrity checks passed. No issues found.';
        }
        
        $issues = [];
        
        // Critical issues (fail status)
        if ($metrics['prices_without_product_count'] > 0) {
            $issues[] = "{$metrics['prices_without_product_count']} orphaned prices (no matching product)";
        }
        if ($metrics['products_without_group_count'] > 0) {
            $issues[] = "{$metrics['products_without_group_count']} orphaned products (no matching group)";
        }
        if ($metrics['groups_duplicates'] > 0) {
            $issues[] = "{$metrics['groups_duplicates']} duplicate groups";
        }
        if ($metrics['products_duplicates'] > 0) {
            $issues[] = "{$metrics['products_duplicates']} duplicate products";
        }
        if ($metrics['prices_duplicates'] > 0) {
            $issues[] = "{$metrics['prices_duplicates']} duplicate prices";
        }
        
        return 'Issues found: ' . implode(', ', $issues);
    }
}
