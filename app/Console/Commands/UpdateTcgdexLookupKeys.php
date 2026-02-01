<?php

namespace App\Console\Commands;

use App\Models\Tcgdx\TcgdxCard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateTcgdexLookupKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tcgdex:update-lookup-keys {--set_id=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update visible_lookup_key field for TCGDEX cards (format: SETCODE 028/64)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting TCGDEX lookup keys update...');
        
        $setId = $this->option('set_id');
        $force = $this->option('force');
        
        // Build query
        $query = TcgdxCard::with('set')
            ->whereNotNull('local_id')
            ->whereNotNull('set_tcgdx_id');
            
        if ($setId) {
            $query->where('set_tcgdx_id', $setId);
        }
        
        if (!$force) {
            $query->whereNull('visible_lookup_key');
        }
        
        $totalCards = $query->count();
        
        if ($totalCards === 0) {
            $this->info('No cards to update.');
            return 0;
        }
        
        $this->info("Found {$totalCards} cards to update");
        $bar = $this->output->createProgressBar($totalCards);
        $bar->start();
        
        $updated = 0;
        $errors = 0;
        
        $query->chunk(500, function ($cards) use ($bar, &$updated, &$errors) {
            foreach ($cards as $card) {
                try {
                    if (!$card->set || !$card->set->card_count_official) {
                        $errors++;
                        $bar->advance();
                        continue;
                    }
                    
                    // Format: "SETCODE 028/64"
                    $setCode = strtoupper($card->set->tcgdex_id);
                    $cardNumber = str_pad($card->local_id, 3, '0', STR_PAD_LEFT);
                    $totalCards = $card->set->card_count_official;
                    
                    $lookupKey = "{$setCode} {$cardNumber}/{$totalCards}";
                    
                    DB::table('tcgdx_cards')
                        ->where('id', $card->id)
                        ->update(['visible_lookup_key' => $lookupKey]);
                    
                    $updated++;
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("Error updating card {$card->id}: " . $e->getMessage());
                }
                
                $bar->advance();
            }
        });
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("✅ Updated: {$updated} cards");
        if ($errors > 0) {
            $this->warn("⚠️  Errors: {$errors} cards");
        }
        
        return 0;
    }
}
