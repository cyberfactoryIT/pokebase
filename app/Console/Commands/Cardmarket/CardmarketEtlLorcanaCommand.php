<?php

namespace App\Console\Commands\Cardmarket;

use Illuminate\Console\Command;

class CardmarketEtlLorcanaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cardmarket:etl-lorcana';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Complete ETL pipeline for Lorcana: Download + Import';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('╔══════════════════════════════════════════════════════════╗');
        $this->info('║   CardMarket ETL Pipeline - Disney Lorcana              ║');
        $this->info('╚══════════════════════════════════════════════════════════╝');
        $this->newLine();

        $startTime = microtime(true);
        $allSuccessful = true;

        // Step 1: Download products & prices
        $this->info('📥 STEP 1/2: Downloading from S3...');
        $this->newLine();
        
        $downloadExitCode = $this->call('cardmarket:download', ['game' => 'lorcana']);
        
        if ($downloadExitCode !== 0) {
            $this->error('Download failed!');
            return self::FAILURE;
        }
        
        $this->newLine();

        // Step 2: Import to database
        $this->info('📦 STEP 2/2: Importing to database...');
        $this->newLine();
        
        $importExitCode = $this->call('cardmarket:import-lorcana');
        
        if ($importExitCode !== 0) {
            $this->error('Import failed!');
            $allSuccessful = false;
        }

        $duration = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════');
        
        if ($allSuccessful) {
            $this->info("✅ ETL Pipeline completed successfully in {$duration}s");
            return self::SUCCESS;
        } else {
            $this->error("❌ ETL Pipeline completed with errors in {$duration}s");
            return self::FAILURE;
        }
    }
}
