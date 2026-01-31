<?php

namespace App\Console\Commands;

use App\Services\Cmapi\CardMarketPriceSyncService;
use Illuminate\Console\Command;

class CardMarketSyncPrices extends Command
{
    protected $signature = 'cardmarket:sync-prices 
                            {--game=lorcana : Game to sync prices for}
                            {--promote : Promote staging data to production after sync}
                            {--clean : Clean old staging data}';

    protected $description = 'Sync CardMarket prices from S3 to staging area and optionally promote to production';

    public function handle(CardMarketPriceSyncService $service)
    {
        $game = $this->option('game');
        
        $this->info("🔄 Downloading CardMarket data for {$game} from S3...");
        
        // Step 1: Download from S3 and import to staging
        $syncStats = $service->importFromS3($game);
        
        $this->info("✅ Import complete:");
        $this->line("   Products imported: {$syncStats['products_imported']}");
        $this->line("   Prices imported: {$syncStats['prices_imported']}");
        $this->line("   Errors: {$syncStats['errors']}");
        
        // Step 2: Promote to production if requested
        if ($this->option('promote')) {
            $this->info("\n📦 Promoting staging data to production...");
            $promoteStats = $service->promoteToProduction($game);
            
            $this->info("✅ Promotion complete:");
            $this->line("   Promoted: {$promoteStats['promoted']}");
            $this->line("   Errors: {$promoteStats['errors']}");
        }
        
        // Step 3: Clean old staging data if requested
        if ($this->option('clean')) {
            $this->info("\n🧹 Cleaning old staging data...");
            $deleted = $service->cleanOldStaging();
            $this->info("✅ Deleted {$deleted} old staging records");
        }
        
        $this->info("\n🎉 All done!");
        
        return 0;
    }
}
