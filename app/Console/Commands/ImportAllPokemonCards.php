<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PokemonImportService;

class ImportAllPokemonCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan pokemon:import-all
     */
    protected $signature = 'pokemon:import-all';

    /**
     * The console command description.
     */
    protected $description = 'Import all Pokémon TCG cards from the Pokemon TCG API into card_catalog';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(PokemonImportService $importService): int
    {
        $this->info('Starting full Pokémon TCG import...');

        try {
            $importService->importAllCards(function (string $message) {
                $this->line($message);
            });
        } catch (\Throwable $e) {
            $this->error('Error during import: ' . $e->getMessage());
            return static::FAILURE;
        }

        $this->info('Pokémon TCG import finished successfully.');
        return static::SUCCESS;
    }
}
