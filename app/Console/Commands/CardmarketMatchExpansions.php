<?php

namespace App\Console\Commands;

use App\Models\CardmarketExpansion;
use App\Models\TcgcsvGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CardmarketMatchExpansions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cardmarket:match-expansions 
                            {--auto-confirm : Automatically confirm high-confidence matches}
                            {--threshold=80 : Minimum similarity threshold (0-100)}
                            {--dry-run : Show matches without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Match Cardmarket expansions with TCGCSV groups using name similarity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threshold = (int) $this->option('threshold');
        $autoConfirm = $this->option('auto-confirm');
        $dryRun = $this->option('dry-run');

        $this->info('🔍 Cardmarket Expansion Matching Tool');
        $this->newLine();

        // Get unmapped Cardmarket expansions
        $unmappedExpansions = CardmarketExpansion::whereNull('tcgcsv_group_id')->get();
        
        if ($unmappedExpansions->isEmpty()) {
            $this->info('✅ All expansions are already mapped!');
            return Command::SUCCESS;
        }

        // Get all TCGCSV groups for Pokemon (game_id = 1)
        $tcgcsvGroups = TcgcsvGroup::where('game_id', 1)->get();

        if ($tcgcsvGroups->isEmpty()) {
            $this->error('❌ No TCGCSV groups found for Pokemon. Please import groups first.');
            return Command::FAILURE;
        }

        $this->info("Found {$unmappedExpansions->count()} unmapped Cardmarket expansions");
        $this->info("Found {$tcgcsvGroups->count()} TCGCSV groups for Pokemon");
        $this->info("Similarity threshold: {$threshold}%");
        $this->newLine();

        $highConfidence = [];
        $mediumConfidence = [];
        $lowConfidence = [];
        $noMatch = [];

        // Calculate similarities
        $progressBar = $this->output->createProgressBar($unmappedExpansions->count());
        $progressBar->start();

        foreach ($unmappedExpansions as $expansion) {
            $bestMatch = $this->findBestMatch($expansion->name, $tcgcsvGroups);
            
            if ($bestMatch['similarity'] >= 90) {
                $highConfidence[] = [
                    'expansion' => $expansion,
                    'match' => $bestMatch
                ];
            } elseif ($bestMatch['similarity'] >= $threshold) {
                $mediumConfidence[] = [
                    'expansion' => $expansion,
                    'match' => $bestMatch
                ];
            } elseif ($bestMatch['similarity'] >= 50) {
                $lowConfidence[] = [
                    'expansion' => $expansion,
                    'match' => $bestMatch
                ];
            } else {
                $noMatch[] = $expansion;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display and process high confidence matches
        if (!empty($highConfidence)) {
            $this->info("🎯 High Confidence Matches (≥90%):");
            $this->displayMatches($highConfidence);
            
            if (!$dryRun) {
                if ($autoConfirm || $this->confirm('Apply these high confidence matches?', true)) {
                    $this->applyMatches($highConfidence);
                    $this->info("✅ Applied " . count($highConfidence) . " high confidence matches");
                }
            }
            $this->newLine();
        }

        // Display and process medium confidence matches
        if (!empty($mediumConfidence)) {
            $this->info("⚠️  Medium Confidence Matches ({$threshold}%-89%):");
            $this->displayMatches($mediumConfidence);
            
            if (!$dryRun && $this->confirm('Apply these medium confidence matches?', false)) {
                $this->applyMatches($mediumConfidence);
                $this->info("✅ Applied " . count($mediumConfidence) . " medium confidence matches");
            }
            $this->newLine();
        }

        // Display low confidence matches
        if (!empty($lowConfidence)) {
            $this->warn("🤔 Low Confidence Matches (50%-{$threshold}%):");
            $this->displayMatches($lowConfidence, 10);
            $this->comment("These require manual review. Not auto-applied.");
            $this->newLine();
        }

        // Display no matches
        if (!empty($noMatch)) {
            $this->error("❌ No Good Matches (<50%):");
            foreach (array_slice($noMatch, 0, 10) as $expansion) {
                $this->line("  - {$expansion->name} (ID: {$expansion->cardmarket_expansion_id})");
            }
            if (count($noMatch) > 10) {
                $this->comment("  ... and " . (count($noMatch) - 10) . " more");
            }
            $this->newLine();
        }

        // Summary
        $this->info('📊 Summary:');
        $this->table(
            ['Category', 'Count'],
            [
                ['High Confidence (≥90%)', count($highConfidence)],
                ['Medium Confidence (' . $threshold . '%-89%)', count($mediumConfidence)],
                ['Low Confidence (50%-' . ($threshold-1) . '%)', count($lowConfidence)],
                ['No Match (<50%)', count($noMatch)],
                ['Total Unmapped', $unmappedExpansions->count()],
            ]
        );

        if ($dryRun) {
            $this->warn('🔸 DRY RUN: No changes were saved to database');
        }

        return Command::SUCCESS;
    }

    /**
     * Find the best matching TCGCSV group for a given expansion name
     */
    private function findBestMatch(string $expansionName, $tcgcsvGroups): array
    {
        $bestSimilarity = 0;
        $bestMatch = null;

        $normalizedExpansion = $this->normalizeString($expansionName);

        foreach ($tcgcsvGroups as $group) {
            $normalizedGroup = $this->normalizeString($group->name);
            
            // Calculate similarity using multiple methods
            $similarity = $this->calculateSimilarity($normalizedExpansion, $normalizedGroup);
            
            if ($similarity > $bestSimilarity) {
                $bestSimilarity = $similarity;
                $bestMatch = $group;
            }
        }

        return [
            'group' => $bestMatch,
            'similarity' => $bestSimilarity
        ];
    }

    /**
     * Calculate similarity between two strings using multiple algorithms
     */
    private function calculateSimilarity(string $str1, string $str2): float
    {
        // Method 1: similar_text percentage
        similar_text($str1, $str2, $percent1);
        
        // Method 2: Levenshtein distance converted to percentage
        $maxLen = max(strlen($str1), strlen($str2));
        if ($maxLen === 0) {
            $percent2 = 100;
        } else {
            $distance = levenshtein(substr($str1, 0, 255), substr($str2, 0, 255));
            $percent2 = (1 - $distance / $maxLen) * 100;
        }
        
        // Method 3: Exact match bonus
        $exactBonus = ($str1 === $str2) ? 10 : 0;
        
        // Method 4: Contains check
        $containsBonus = 0;
        if (str_contains($str1, $str2) || str_contains($str2, $str1)) {
            $containsBonus = 5;
        }
        
        // Weighted average
        $similarity = ($percent1 * 0.5) + ($percent2 * 0.5) + $exactBonus + $containsBonus;
        
        return min(100, $similarity);
    }

    /**
     * Normalize string for better comparison
     */
    private function normalizeString(string $str): string
    {
        $normalized = Str::lower($str);
        
        // Remove TCGCSV prefixes (SWSH10:, SV03:, SM -, XY -, etc.)
        $normalized = preg_replace('/^(swsh\d+|sv\d*|sm|xy|dp|hs|bw|ex)\s*[-:]?\s*/i', '', $normalized);
        
        // Remove common variations
        $normalized = str_replace(['&', ' and ', ' - ', ': ', '  '], ' ', $normalized);
        $normalized = str_replace(['pokemon', 'tcg', 'cards', 'set', 'base'], '', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $normalized = trim($normalized);
        
        return $normalized;
    }

    /**
     * Display matches in a formatted table
     */
    private function displayMatches(array $matches, int $limit = null): void
    {
        $displayMatches = $limit ? array_slice($matches, 0, $limit) : $matches;
        
        $rows = [];
        foreach ($displayMatches as $match) {
            $rows[] = [
                $match['expansion']->name,
                $match['match']['group']->name,
                number_format($match['match']['similarity'], 1) . '%',
                $match['match']['group']->group_id,
            ];
        }

        $this->table(
            ['Cardmarket Expansion', 'TCGCSV Group', 'Match %', 'Group ID'],
            $rows
        );

        if ($limit && count($matches) > $limit) {
            $this->comment("  ... and " . (count($matches) - $limit) . " more");
        }
    }

    /**
     * Apply matches to database
     */
    private function applyMatches(array $matches): void
    {
        foreach ($matches as $match) {
            $match['expansion']->update([
                'tcgcsv_group_id' => $match['match']['group']->group_id
            ]);
        }
    }
}
