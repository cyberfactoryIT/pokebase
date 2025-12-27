<?php

namespace App\Console\Commands;

use App\Models\CardmarketProduct;
use App\Models\TcgcsvProduct;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CardmarketMatchProducts extends Command
{
    protected $signature = 'cardmarket:match-products 
                            {--expansion= : Match only specific expansion ID}
                            {--auto-confirm : Automatically confirm high-confidence matches}
                            {--threshold=85 : Minimum similarity threshold (0-100)}
                            {--dry-run : Show matches without saving}
                            {--limit=1000 : Limit number of products to process}';

    protected $description = 'Match Cardmarket products with TCGCSV products using name similarity';

    public function handle()
    {
        $threshold = (int) $this->option('threshold');
        $autoConfirm = $this->option('auto-confirm');
        $dryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $expansionId = $this->option('expansion');

        $this->info('🔍 Cardmarket Product Matching Tool');
        $this->newLine();

        // Get unmapped Cardmarket products with mapped expansions
        $query = CardmarketProduct::whereNull('tcgcsv_product_id')
            ->whereHas('expansion', function($q) {
                $q->whereNotNull('tcgcsv_group_id');
            })
            ->with('expansion');

        if ($expansionId) {
            $query->where('id_expansion', $expansionId);
        }

        $unmappedProducts = $query->limit($limit)->get();
        
        if ($unmappedProducts->isEmpty()) {
            $this->info('✅ All products are already mapped (or no mappable expansions)!');
            return Command::SUCCESS;
        }

        $this->info("Found {$unmappedProducts->count()} unmapped Cardmarket products");
        $this->info("Similarity threshold: {$threshold}%");
        $this->newLine();

        $highConfidence = [];
        $mediumConfidence = [];
        $lowConfidence = [];
        $noMatch = [];

        $progressBar = $this->output->createProgressBar($unmappedProducts->count());
        $progressBar->start();

        foreach ($unmappedProducts as $product) {
            $groupId = $product->expansion->tcgcsv_group_id;
            
            // Get TCGCSV products in same group
            $tcgcsvProducts = TcgcsvProduct::where('game_id', 1)
                ->where('group_id', $groupId)
                ->get();

            if ($tcgcsvProducts->isEmpty()) {
                $noMatch[] = $product;
                $progressBar->advance();
                continue;
            }

            $bestMatch = $this->findBestMatch($product, $tcgcsvProducts);
            
            if ($bestMatch['similarity'] >= 95) {
                $highConfidence[] = [
                    'product' => $product,
                    'match' => $bestMatch
                ];
            } elseif ($bestMatch['similarity'] >= $threshold) {
                $mediumConfidence[] = [
                    'product' => $product,
                    'match' => $bestMatch
                ];
            } elseif ($bestMatch['similarity'] >= 50) {
                $lowConfidence[] = [
                    'product' => $product,
                    'match' => $bestMatch
                ];
            } else {
                $noMatch[] = $product;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display and process high confidence matches
        if (!empty($highConfidence)) {
            $this->info("🎯 High Confidence Matches (≥95%):");
            $this->displayMatches($highConfidence, 20);
            
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
            $this->info("⚠️  Medium Confidence Matches ({$threshold}%-94%):");
            $this->displayMatches($mediumConfidence, 20);
            
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

        // Summary
        $this->info('📊 Summary:');
        $this->table(
            ['Category', 'Count'],
            [
                ['High Confidence (≥95%)', count($highConfidence)],
                ['Medium Confidence (' . $threshold . '%-94%)', count($mediumConfidence)],
                ['Low Confidence (50%-' . ($threshold-1) . '%)', count($lowConfidence)],
                ['No Match (<50%)', count($noMatch)],
                ['Total Processed', $unmappedProducts->count()],
            ]
        );

        if ($dryRun) {
            $this->warn('🔸 DRY RUN: No changes were saved to database');
        }

        return Command::SUCCESS;
    }

    private function findBestMatch($cardmarketProduct, $tcgcsvProducts): array
    {
        $bestSimilarity = 0;
        $bestMatch = null;

        $normalizedCM = $this->normalizeProductName($cardmarketProduct->name);

        foreach ($tcgcsvProducts as $tcgProduct) {
            $normalizedTCG = $this->normalizeProductName($tcgProduct->name);
            
            $similarity = $this->calculateSimilarity($normalizedCM, $normalizedTCG);
            
            if ($similarity > $bestSimilarity) {
                $bestSimilarity = $similarity;
                $bestMatch = $tcgProduct;
            }
        }

        return [
            'product' => $bestMatch,
            'similarity' => $bestSimilarity
        ];
    }

    private function calculateSimilarity(string $str1, string $str2): float
    {
        // Method 1: similar_text percentage
        similar_text($str1, $str2, $percent1);
        
        // Method 2: Levenshtein distance
        $maxLen = max(strlen($str1), strlen($str2));
        if ($maxLen === 0) {
            $percent2 = 100;
        } else {
            $distance = levenshtein(substr($str1, 0, 255), substr($str2, 0, 255));
            $percent2 = (1 - $distance / $maxLen) * 100;
        }
        
        // Method 3: Exact match bonus
        $exactBonus = ($str1 === $str2) ? 10 : 0;
        
        // Method 4: Word overlap
        $words1 = explode(' ', $str1);
        $words2 = explode(' ', $str2);
        $commonWords = count(array_intersect($words1, $words2));
        $totalWords = max(count($words1), count($words2));
        $wordOverlap = $totalWords > 0 ? ($commonWords / $totalWords) * 100 : 0;
        
        // Weighted average
        $similarity = ($percent1 * 0.3) + ($percent2 * 0.3) + ($wordOverlap * 0.3) + $exactBonus + 10;
        
        return min(100, $similarity);
    }

    private function normalizeProductName(string $name): string
    {
        $normalized = Str::lower($name);
        
        // Remove content in brackets/parentheses (abilities, attacks)
        $normalized = preg_replace('/\[.*?\]/', '', $normalized);
        $normalized = preg_replace('/\(.*?\)/', '', $normalized);
        
        // Remove common card type indicators
        $normalized = str_replace(['ex', 'gx', 'vmax', 'vstar', 'v', 'mega', 'prism star', '◇'], '', $normalized);
        
        // Remove special characters
        $normalized = preg_replace('/[^a-z0-9\s]/', '', $normalized);
        
        // Remove multiple spaces
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        
        return trim($normalized);
    }

    private function displayMatches(array $matches, int $limit = null): void
    {
        $displayMatches = $limit ? array_slice($matches, 0, $limit) : $matches;
        
        $rows = [];
        foreach ($displayMatches as $match) {
            $rows[] = [
                Str::limit($match['product']->name, 30),
                Str::limit($match['match']['product']->name, 30),
                $match['product']->expansion->name,
                number_format($match['match']['similarity'], 1) . '%',
            ];
        }

        $this->table(
            ['Cardmarket Product', 'TCGCSV Product', 'Expansion', 'Match %'],
            $rows
        );

        if ($limit && count($matches) > $limit) {
            $this->comment("  ... and " . (count($matches) - $limit) . " more");
        }
    }

    private function applyMatches(array $matches): void
    {
        foreach ($matches as $match) {
            $match['product']->update([
                'tcgcsv_product_id' => $match['match']['product']->product_id
            ]);
        }
    }
}
