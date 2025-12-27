<?php

namespace App\Console\Commands\Cardmarket;

use App\Services\Cardmarket\CardmarketDownloader;
use Illuminate\Console\Command;

class CardmarketDownloadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cardmarket:download 
                            {game? : Game to download (pokemon, mtg, yugioh). Defaults to config default_game}
                            {--products : Download only products}
                            {--prices : Download only prices}
                            {--force : Force download even if cached version exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download Cardmarket product and price files for a game';

    protected CardmarketDownloader $downloader;

    /**
     * Create a new command instance.
     */
    public function __construct(CardmarketDownloader $downloader)
    {
        parent::__construct();
        $this->downloader = $downloader;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $game = $this->argument('game') ?? config('cardmarket.default_game');
        $downloadProducts = $this->option('products');
        $downloadPrices = $this->option('prices');
        $force = $this->option('force');

        // Validate game
        if (!config("cardmarket.games.{$game}")) {
            $this->error("Invalid game: {$game}");
            $this->info("Available games: " . implode(', ', array_keys(config('cardmarket.games'))));
            return self::FAILURE;
        }

        // If no specific option, download both
        if (!$downloadProducts && !$downloadPrices) {
            $downloadProducts = true;
            $downloadPrices = true;
        }

        $gameName = config("cardmarket.games.{$game}.name");
        $this->info("Starting Cardmarket download for {$gameName}...");
        $this->newLine();

        $allSuccessful = true;

        // Download products
        if ($downloadProducts) {
            $this->info('📦 Downloading products...');
            $result = $this->downloader->downloadProducts($game, $force);

            if ($result['success']) {
                $cached = $result['cached'] ?? false;
                $icon = $cached ? '♻️' : '✅';
                $this->info("{$icon} {$result['message']}");
                
                if (!$cached && isset($result['size'], $result['hash'])) {
                    $this->line("   Size: " . number_format($result['size'] / 1024 / 1024, 2) . " MB");
                    $this->line("   Hash: {$result['hash']}");
                }
                
                if (isset($result['json_version'], $result['created_at'])) {
                    $this->line("   Version: {$result['json_version']}");
                    $this->line("   Created: {$result['created_at']}");
                }
            } else {
                $this->error("❌ {$result['message']}");
                $allSuccessful = false;
            }

            $this->newLine();
        }

        // Download prices
        if ($downloadPrices) {
            $this->info('💰 Downloading prices...');
            $result = $this->downloader->downloadPrices($game, $force);

            if ($result['success']) {
                $cached = $result['cached'] ?? false;
                $icon = $cached ? '♻️' : '✅';
                $this->info("{$icon} {$result['message']}");
                
                if (!$cached && isset($result['size'], $result['hash'])) {
                    $this->line("   Size: " . number_format($result['size'] / 1024 / 1024, 2) . " MB");
                    $this->line("   Hash: {$result['hash']}");
                }
                
                if (isset($result['json_version'], $result['created_at'])) {
                    $this->line("   Version: {$result['json_version']}");
                    $this->line("   Created: {$result['created_at']}");
                }
            } else {
                $this->error("❌ {$result['message']}");
                $allSuccessful = false;
            }

            $this->newLine();
        }

        if ($allSuccessful) {
            $this->info('🎉 All downloads completed successfully!');
            return self::SUCCESS;
        } else {
            $this->error('⚠️  Some downloads failed. Check the logs for details.');
            return self::FAILURE;
        }
    }
}
