<?php

namespace App\Console\Commands;

use App\Models\CardmarketExpansion;
use App\Models\CardmarketProduct;
use App\Models\TcgcsvProduct;
use App\Models\TcgcsvCardmarketMapping;
use Illuminate\Console\Command;

class CardmarketMatchMetacards extends Command
{
    protected $signature = 'cardmarket:match-metacards
                            {--expansion= : Only match products from specific TCGCSV group_id}
                            {--threshold=85 : Minimum similarity score (0-100)}
                            {--auto-confirm : Automatically apply high-confidence matches}
                            {--dry-run : Show matches without saving}
                            {--limit= : Maximum products to process (default: all)}';

    protected $description = 'Fuzzy match TCGCSV products to Cardmarket products (sets cardmarket_product_id)';

    public function handle(): int
    {
        $expansionId = $this->option('expansion');
        $threshold = (float) $this->option('threshold');
        $autoConfirm = $this->option('auto-confirm');
        $dryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be saved');
        }

        // PHASE 1: Direct mapping from RapidAPI cardmarket_id
        $this->info('🎯 PHASE 1: Direct mapping from RapidAPI cardmarket_id...');
        $directMapped = $this->mapFromRapidAPI($dryRun);
        $this->info("✅ Mapped {$directMapped} products directly from RapidAPI");
        $this->newLine();

        // PHASE 2: Fuzzy matching for remaining products
        $this->info('🔍 PHASE 2: Fuzzy matching for remaining unmapped products...');
        $this->newLine();

        // Get unmapped TCGCSV products (without cardmarket_product_id)
        $query = TcgcsvProduct::whereNull('cardmarket_product_id')
            ->where('game_id', 1); // Pokemon only for now

        if ($expansionId) {
            $query->where('group_id', $expansionId);
        }

        if ($limit) {
            $query->limit($limit);
        }

        $tcgcsvProducts = $query->get();

        if ($tcgcsvProducts->isEmpty()) {
            $this->info('✅ No unmapped products found!');
            return self::SUCCESS;
        }

        $this->info("📦 Processing {$tcgcsvProducts->count()} TCGCSV products...");
        $this->newLine();

        $stats = [
            'processed' => 0,
            'high_confidence' => 0,
            'medium_confidence' => 0,
            'low_confidence' => 0,
            'no_match' => 0,
            'saved' => 0,
            'direct_mapped' => $directMapped,
        ];

        $progressBar = $this->output->createProgressBar($tcgcsvProducts->count());
        $progressBar->start();

        foreach ($tcgcsvProducts as $tcgcsvProduct) {
            $stats['processed']++;

            // Search only for Cardmarket products with similar names (optimization)
            // Extract first word of card name for initial filtering
            $firstWord = explode(' ', $tcgcsvProduct->name)[0];
            
            $cardmarketProducts = CardmarketProduct::where('name', 'like', $firstWord . '%')
                ->select('cardmarket_product_id', 'id_metacard', 'name')
                ->get()
                ->take(100); // Limit to first 100 products

            if ($cardmarketProducts->isEmpty()) {
                $stats['no_match']++;
                $progressBar->advance();
                continue;
            }

            // Find best match
            $bestMatch = null;
            $bestScore = 0;

            foreach ($cardmarketProducts as $cmProduct) {
                $score = $this->calculateSimilarity(
                    $tcgcsvProduct->name,
                    $cmProduct->name
                );

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $cmProduct;
                }
            }

            // Classify match
            if ($bestScore >= 95) {
                $stats['high_confidence']++;
                $matchType = 'high';
            } elseif ($bestScore >= $threshold) {
                $stats['medium_confidence']++;
                $matchType = 'medium';
            } elseif ($bestScore >= 50) {
                $stats['low_confidence']++;
                $matchType = 'low';
            } else {
                $stats['no_match']++;
                $progressBar->advance();
                continue;
            }

            // Save high confidence matches if auto-confirm is enabled
            if (!$dryRun && $autoConfirm && $bestScore >= 95) {
                TcgcsvProduct::where('product_id', $tcgcsvProduct->product_id)
                    ->update(['cardmarket_product_id' => $bestMatch->cardmarket_product_id]);
                $stats['saved']++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        $this->info('📊 Matching Results:');
        $this->newLine();

        $this->table(
            ['Category', 'Count', 'Percentage'],
            [
                ['Total Processed', $stats['processed'], '100%'],
                ['High Confidence (≥95%)', $stats['high_confidence'], $this->percentage($stats['high_confidence'], $stats['processed'])],
                ['Medium Confidence (' . $threshold . '-94%)', $stats['medium_confidence'], $this->percentage($stats['medium_confidence'], $stats['processed'])],
                ['Low Confidence (50-' . ($threshold - 1) . '%)', $stats['low_confidence'], $this->percentage($stats['low_confidence'], $stats['processed'])],
                ['No Match (<50%)', $stats['no_match'], $this->percentage($stats['no_match'], $stats['processed'])],
            ]
        );

        $this->newLine();

        if ($dryRun) {
            $this->warn('🔍 DRY RUN: No changes were saved');
            $this->info("Would have saved {$stats['high_confidence']} high-confidence matches");
        } elseif ($autoConfirm) {
            $this->info("✅ Saved {$stats['saved']} high-confidence matches to cardmarket_product_id");
        } else {
            $this->warn('💡 Use --auto-confirm to save high-confidence matches automatically');
        }

        return self::SUCCESS;
    }

    private function calculateSimilarity(string $name1, string $name2): float
    {
        // Normalize names
        $name1 = $this->normalizeName($name1);
        $name2 = $this->normalizeName($name2);

        // Similar text percentage (30%)
        similar_text($name1, $name2, $percent1);

        // Levenshtein distance (30%)
        $maxLen = max(strlen($name1), strlen($name2));
        $lev = levenshtein(substr($name1, 0, 255), substr($name2, 0, 255));
        $percent2 = (1 - ($lev / $maxLen)) * 100;

        // Word overlap (30%)
        $words1 = explode(' ', $name1);
        $words2 = explode(' ', $name2);
        $commonWords = count(array_intersect($words1, $words2));
        $totalWords = count(array_unique(array_merge($words1, $words2)));
        $percent3 = $totalWords > 0 ? ($commonWords / $totalWords) * 100 : 0;

        // Exact match bonus (10%)
        $exactBonus = ($name1 === $name2) ? 10 : 0;

        return ($percent1 * 0.3) + ($percent2 * 0.3) + ($percent3 * 0.3) + $exactBonus;
    }

    private function normalizeName(string $name): string
    {
        $name = strtolower($name);
        
        // Remove brackets and content
        $name = preg_replace('/\[.*?\]/', '', $name);
        $name = preg_replace('/\(.*?\)/', '', $name);
        
        // Remove card type indicators
        $name = str_replace(['ex', 'gx', 'vmax', 'vstar', 'v', 'mega', 'prism star', '◇'], '', $name);
        
        // Remove special characters
        $name = preg_replace('/[^a-z0-9\s]/', '', $name);
        
        // Collapse multiple spaces
        $name = preg_replace('/\s+/', ' ', $name);
        
        return trim($name);
    }

    private function percentage(int $value, int $total): string
    {
        if ($total === 0) return '0%';
        return round(($value / $total) * 100, 1) . '%';
    }

    private function mapFromRapidAPI(bool $dryRun): int
    {
        $mapped = 0;

        // Use chunking to avoid memory exhaustion with large datasets
        TcgcsvProduct::whereNull('cardmarket_product_id')
            ->whereNotNull('rapidapi_card_id')
            ->chunk(100, function($products) use (&$mapped, $dryRun) {
                foreach ($products as $product) {
                    $cardmarketId = $product->rapidapiCard->cardmarket_id ?? null;

                    if (!$cardmarketId) {
                        continue;
                    }

                    // Verify it exists in cardmarket_products
                    $cardmarketProduct = \App\Models\CardmarketProduct::where('cardmarket_product_id', $cardmarketId)
                        ->first();

                    if (!$cardmarketProduct) {
                        continue;
                    }

                    if (!$dryRun) {
                        TcgcsvProduct::where('product_id', $product->product_id)
                            ->update(['cardmarket_product_id' => $cardmarketId]);
                    }

                    $mapped++;
                }
            });

        return $mapped;
    }
}
