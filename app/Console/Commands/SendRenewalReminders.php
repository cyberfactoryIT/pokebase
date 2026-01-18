<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\User;
use App\Notifications\SubscriptionRenewalReminder;
use Illuminate\Console\Command;

class SendRenewalReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:send-renewal-reminders {--days=7 : Days before renewal to send reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders to users whose subscription will renew soon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysAhead = (int) $this->option('days');
        
        $this->info("Checking for subscriptions renewing in {$daysAhead} days...");

        // Find organizations with active subscriptions renewing soon
        $targetDate = now()->addDays($daysAhead)->startOfDay();
        
        $organizations = Organization::whereNotNull('renew_date')
            ->whereNotNull('pricing_plan_id')
            ->where('subscription_cancelled', 0)
            ->whereDate('renew_date', $targetDate->toDateString())
            ->with('pricingPlan')
            ->get();

        if ($organizations->isEmpty()) {
            $this->info('No subscriptions found renewing in ' . $daysAhead . ' days.');
            return 0;
        }

        $this->info("Found {$organizations->count()} subscription(s) to remind.");

        $sentCount = 0;
        $failedCount = 0;

        foreach ($organizations as $organization) {
            try {
                // Find organization admin/owner
                $users = User::where('organization_id', $organization->id)
                    ->whereHas('roles', function($query) {
                        $query->where('name', 'admin');
                    })
                    ->get();

                if ($users->isEmpty()) {
                    // If no admin, notify all users in the organization
                    $users = User::where('organization_id', $organization->id)->get();
                }

                foreach ($users as $user) {
                    $user->notify(new SubscriptionRenewalReminder($organization, $daysAhead));
                    $this->line("✓ Sent reminder to {$user->email} for organization #{$organization->id}");
                    $sentCount++;
                }

            } catch (\Exception $e) {
                $this->error("✗ Failed to send reminder for organization #{$organization->id}: " . $e->getMessage());
                $failedCount++;
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("- Reminders sent: {$sentCount}");
        if ($failedCount > 0) {
            $this->warn("- Failed: {$failedCount}");
        }

        return 0;
    }
}
