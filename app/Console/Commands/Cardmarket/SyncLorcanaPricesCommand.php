<?php

namespace App\Console\Commands\Cardmarket;

use App\Models\Cmapi\CmapiCard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncLorcanaPricesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cardmarket:sync-lorcana-prices 
                            {--force : Force update all cards regardless of last update}
                            {--date= : Specific date to sync prices from (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Lorcana prices from cardmarket_price_quotes_lorcana to cmapi_cards';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');
        $date = $this->option('date') ?? date('Y-m-d');

        $this->info('💰 Syncing Lorcana Prices from CardMarket');
        $this->info("📅 Using price data from: {$date}");
        $this->newLine();

        try {
            // Use raw SQL for better performance with JOIN
            $sql = "
                UPDATE cmapi_cards cc
                INNER JOIN cardmarket_products_lorcana cpl ON cc.id = cpl.cmapi_card_id
                INNER JOIN cardmarket_price_quotes_lorcana cpq ON cpl.cardmarket_product_id = cpq.cardmarket_product_id
                SET 
                    cc.price_eur = COALESCE(cpq.trend, cpq.avg, cpq.low),
                    cc.updated_at = NOW()
                WHERE cc.game = 'lorcana'
                AND cpq.as_of_date = ?
                AND COALESCE(cpq.trend, cpq.avg, cpq.low) IS NOT NULL
            ";

            $bindings = [$date];

            // Add force condition
            if (!$force) {
                $sql .= " AND (cc.updated_at IS NULL OR cc.updated_at < DATE_SUB(NOW(), INTERVAL 1 DAY))";
            }

            $updated = DB::update($sql, $bindings);

            $this->info("✅ Successfully updated {$updated} Lorcana cards with latest prices");
            
            // Show some statistics
            $totalLorcana = CmapiCard::where('game', 'lorcana')->count();
            $withPrices = CmapiCard::where('game', 'lorcana')
                ->whereNotNull('price_eur')
                ->count();
            
            $this->newLine();
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Lorcana Cards', $totalLorcana],
                    ['Cards with Prices', $withPrices],
                    ['Coverage', round(($withPrices / max($totalLorcana, 1)) * 100, 1) . '%'],
                    ['Updated Today', $updated],
                ]
            );

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Price sync failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return self::FAILURE;
        }
    }
}
