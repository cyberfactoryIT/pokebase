<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DeckEvaluationEntitlementService;

class MarkExpiredDeckEvaluationPurchases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deck-evaluation:mark-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark expired deck evaluation purchases';

    private DeckEvaluationEntitlementService $entitlementService;

    public function __construct(DeckEvaluationEntitlementService $entitlementService)
    {
        parent::__construct();
        $this->entitlementService = $entitlementService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Marking expired deck evaluation purchases...');

        $count = $this->entitlementService->markExpiredPurchases();

        $this->info("Marked {$count} purchases as expired.");

        return Command::SUCCESS;
    }
}
