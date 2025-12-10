<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PokemonSet;

class ListPokemonSets extends Command
{
    protected $signature = 'pokemon:list-sets 
                            {--status= : Filter by import status (never|success|failed|in_progress)}
                            {--series= : Filter by series}
                            {--needs-reimport : Show only sets that need reimport}';

    protected $description = 'List Pokemon TCG sets with their import status';

    public function handle(): int
    {
        $query = PokemonSet::query()->orderBy('release_date', 'desc');

        // Applica filtri
        if ($status = $this->option('status')) {
            switch ($status) {
                case 'never':
                    $query->neverImported();
                    break;
                case 'success':
                    $query->successful();
                    break;
                case 'failed':
                    $query->failedImport();
                    break;
                case 'in_progress':
                    $query->inProgress();
                    break;
            }
        }

        if ($series = $this->option('series')) {
            $query->where('series', $series);
        }

        $sets = $query->get();

        if ($this->option('needs-reimport')) {
            $sets = $sets->filter(fn($set) => $set->needsReimport());
        }

        if ($sets->isEmpty()) {
            $this->info('No sets found with the specified criteria');
            return static::SUCCESS;
        }

        $this->info('Pokemon TCG Sets (' . $sets->count() . ' found)');
        $this->newLine();

        $statusEmojis = [
            'never' => '⚪',
            'success' => '✅',
            'failed' => '❌',
            'in_progress' => '🟡',
        ];

        $data = $sets->map(function ($set) use ($statusEmojis) {
            return [
                $set->set_id,
                $set->name,
                $set->series ?? 'N/A',
                $set->total ?? 'N/A',
                $set->release_date?->format('Y-m-d') ?? 'N/A',
                $statusEmojis[$set->last_import_status] . ' ' . $set->last_import_status,
                $set->cards_imported,
                $set->last_import_at?->format('Y-m-d H:i') ?? 'Never',
                $set->needsReimport() ? '⚠️ Yes' : '',
            ];
        });

        $this->table(
            ['Set ID', 'Name', 'Series', 'Total', 'Release', 'Status', 'Imported', 'Last Import', 'Needs Update'],
            $data
        );

        // Mostra statistiche
        $this->newLine();
        $stats = [
            ['Never Imported', $sets->where('last_import_status', 'never')->count()],
            ['Successful', $sets->where('last_import_status', 'success')->count()],
            ['Failed', $sets->where('last_import_status', 'failed')->count()],
            ['In Progress', $sets->where('last_import_status', 'in_progress')->count()],
            ['Needs Reimport', $sets->filter(fn($s) => $s->needsReimport())->count()],
        ];

        $this->table(['Status', 'Count'], $stats);

        return static::SUCCESS;
    }
}
