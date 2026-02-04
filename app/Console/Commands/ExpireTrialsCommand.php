<?php

namespace App\Console\Commands;

use App\Models\Organization;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ExpireTrialsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trials:expire {--dry-run : Show what would be expired without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire trials that have reached their end date and revert organizations to free plan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }
        
        // Find all organizations with expired trials
        $expiredTrials = Organization::whereNotNull('trial_plan_id')
            ->whereNotNull('trial_expires_at')
            ->where('trial_expires_at', '<=', now())
            ->get();
            
        if ($expiredTrials->isEmpty()) {
            $this->info('✅ No expired trials found');
            return 0;
        }
        
        $this->info("Found {$expiredTrials->count()} expired trial(s)");
        $this->newLine();
        
        foreach ($expiredTrials as $org) {
            $planName = $org->trialPlan ? $org->trialPlan->name : 'Unknown';
            $expiredDate = $org->trial_expires_at->format('Y-m-d H:i:s');
            
            $this->line("📦 Organization: {$org->name} (ID: {$org->id})");
            $this->line("   Trial Plan: {$planName}");
            $this->line("   Expired: {$expiredDate}");
            
            if (!$dryRun) {
                $org->endTrial();
                $this->info("   ✅ Trial ended - reverted to free plan");
                
                // TODO: Send notification email to user about trial expiration
                // and offer to upgrade to paid subscription
            } else {
                $this->comment("   [DRY RUN] Would end trial");
            }
            
            $this->newLine();
        }
        
        if ($dryRun) {
            $this->warn("🔍 DRY RUN - No changes were made. Run without --dry-run to expire trials.");
        } else {
            $this->info("✅ Successfully expired {$expiredTrials->count()} trial(s)");
        }
        
        return 0;
    }
}
