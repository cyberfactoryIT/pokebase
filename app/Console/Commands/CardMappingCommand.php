<?php

namespace App\Console\Commands;

use App\Services\CardMappingService;
use Illuminate\Console\Command;

class CardMappingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cards:map 
                            {game=pokemon : Game to map (pokemon, mtg, yugioh)}
                            {--stats : Show statistics only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Map RapidAPI cards to TCGCSV products';

    protected CardMappingService $service;

    /**
     * Create a new command instance.
     */
    public function __construct(CardMappingService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $game = $this->argument('game');
        $showStatsOnly = $this->option('stats');

        if ($showStatsOnly) {
            $this->showStatistics($game);
            return self::SUCCESS;
        }

        $this->info("🔗 Mapping {$game} cards...");
        $this->newLine();

        $stats = $this->service->mapRapidApiToTcgcsv($game);

        $this->info("✅ Mapping completed!");
        $this->newLine();

        $this->line("📊 Results:");
        $this->line("   Total RapidAPI cards: {$stats['total_rapid']}");
        $this->line("   Successfully mapped: {$stats['mapped']}");
        
        if (!empty($stats['by_method'])) {
            $this->newLine();
            $this->line("   By method:");
            foreach ($stats['by_method'] as $method => $count) {
                $this->line("      - {$method}: {$count}");
            }
        }

        $this->newLine();
        $this->showStatistics($game);

        return self::SUCCESS;
    }

    /**
     * Show mapping statistics
     */
    protected function showStatistics(string $game): void
    {
        $stats = $this->service->getStatistics($game);

        $this->info("📊 Current Mapping Statistics for {$game}:");
        $this->line("   Total mappings: {$stats['total_mappings']}");
        $this->line("   Average confidence: {$stats['avg_confidence']}");
        
        if (!empty($stats['by_method'])) {
            $this->newLine();
            $this->line("   By method:");
            foreach ($stats['by_method'] as $method => $count) {
                $this->line("      - {$method}: {$count}");
            }
        }
    }
}
