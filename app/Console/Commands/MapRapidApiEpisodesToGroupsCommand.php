<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\PipelineRun;

class MapRapidApiEpisodesToGroupsCommand extends Command
{
    protected $signature = 'rapidapi:map-episodes 
                            {--dry-run : Show matches without updating}
                            {--force : Update existing logo_url}';

    protected $description = 'Map RapidAPI episodes to TCGCSV groups using set codes and logos';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $pipelineRun = PipelineRun::start('rapidapi:map-episodes', [
            'dry_run' => $dryRun,
            'force' => $force,
        ]);

        $this->info('🔗 Mapping RapidAPI Episodes to TCGCSV Groups...');
        $this->newLine();

        // Get all RapidAPI episodes with logos
        $episodes = DB::table('rapidapi_episodes')
            ->whereNotNull('logo_url')
            ->where('game', 'pokemon')
            ->get();

        if ($episodes->isEmpty()) {
            $this->warn('No RapidAPI episodes found with logos.');
            return self::FAILURE;
        }

        $this->line("Found {$episodes->count()} episodes with logos");
        $this->newLine();

        $matched = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($episodes as $episode) {
            // Try to match by abbreviation code
            $query = DB::table('tcgcsv_groups')
                ->where('category_id', 3); // Pokemon only

            if ($episode->code) {
                $query->where(function ($q) use ($episode) {
                    $q->where('abbreviation', $episode->code)
                      ->orWhere('abbreviation', 'like', $episode->code . '%')
                      ->orWhere('abbreviation', 'like', '%' . $episode->code);
                });
            } else {
                // Fallback to name matching
                $query->where('name', 'like', '%' . $episode->name . '%');
            }

            $group = $query->first();

            if ($group) {
                $matched++;
                
                $this->line("✓ Match: {$episode->name} ({$episode->code}) → {$group->name} ({$group->abbreviation})");
                
                // Check if update needed
                if (!$dryRun) {
                    $shouldUpdate = $force || empty($group->logo_url);
                    
                    if ($shouldUpdate) {
                        DB::table('tcgcsv_groups')
                            ->where('group_id', $group->group_id)
                            ->update([
                                'logo_url' => $episode->logo_url,
                                'rapidapi_episode_id' => $episode->episode_id,
                                'updated_at' => now(),
                            ]);
                        
                        $updated++;
                        $this->line("  → Updated logo: " . substr($episode->logo_url, 0, 60) . "...");
                    } else {
                        $skipped++;
                        $this->line("  → Skipped (already has logo)");
                    }
                }
            } else {
                $this->warn("✗ No match: {$episode->name} ({$episode->code})");
            }
        }

        $this->newLine();
        $this->info("📊 Summary:");
        $this->line("   Matched: {$matched}/{$episodes->count()}");
        
        if (!$dryRun) {
            $this->line("   Updated: {$updated}");
            $this->line("   Skipped: {$skipped}");
        } else {
            $this->warn("   (Dry run - no changes made)");
        }

        // Mark pipeline run as success
        $pipelineRun->markSuccess([
            'rows_processed' => $episodes->count(),
            'rows_updated' => $dryRun ? 0 : $updated,
        ]);

        return self::SUCCESS;
    }
}
