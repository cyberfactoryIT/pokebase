<?php

namespace App\Services;

use Illuminate\Support\Collection;

class CollectionInsightsService
{
    /**
     * Generate insight for rarity distribution
     *
     * @param Collection $rarityDistribution
     * @return string|null
     */
    public function generateRarityInsight(Collection $rarityDistribution): ?string
    {
        if ($rarityDistribution->isEmpty()) {
            return null;
        }

        $total = $rarityDistribution->sum('total_quantity');
        $topTwo = $rarityDistribution->sortByDesc('total_quantity')->take(2);
        
        if ($topTwo->count() === 0) {
            return null;
        }

        $topRarity = $topTwo->first();
        $topPercentage = round(($topRarity->total_quantity / $total) * 100);

        // If one rarity dominates (>50%)
        if ($topPercentage > 50) {
            return __('stats_insights.rarity.dominant', ['rarity' => $topRarity->rarity ?: 'Unknown']);
        }

        // If top two combined are significant
        if ($topTwo->count() >= 2) {
            $secondRarity = $topTwo->skip(1)->first();
            $combinedPercentage = round((($topRarity->total_quantity + $secondRarity->total_quantity) / $total) * 100);
            
            if ($combinedPercentage > 60) {
                return __('stats_insights.rarity.skewed', [
                    'rarity1' => $topRarity->rarity ?: 'Unknown',
                    'rarity2' => $secondRarity->rarity ?: 'Unknown'
                ]);
            }
        }

        // Balanced distribution
        return __('stats_insights.rarity.balanced');
    }

    /**
     * Generate insight for condition distribution
     *
     * @param Collection $conditionDistribution
     * @return string|null
     */
    public function generateConditionInsight(Collection $conditionDistribution): ?string
    {
        if ($conditionDistribution->isEmpty()) {
            return null;
        }

        $total = $conditionDistribution->sum('total_quantity');
        $dominant = $conditionDistribution->sortByDesc('total_quantity')->first();
        
        if (!$dominant) {
            return null;
        }

        $dominantPercentage = round(($dominant->total_quantity / $total) * 100);

        // One condition is dominant (>60%)
        if ($dominantPercentage > 60) {
            $conditionLabel = $this->formatCondition($dominant->condition);
            return __('stats_insights.condition.dominant', ['condition' => $conditionLabel]);
        }

        // Balanced mix
        return __('stats_insights.condition.balanced');
    }

    /**
     * Generate insight for top sets
     *
     * @param Collection $topSets
     * @param array $focusSet
     * @return string|null
     */
    public function generateSetsInsight(Collection $topSets, array $focusSet): ?string
    {
        if ($topSets->isEmpty()) {
            return null;
        }

        // If we have a focus set with meaningful completion
        if (!empty($focusSet) && $focusSet['completion_percentage'] > 20) {
            return __('stats_insights.sets.focus_candidate', ['set' => $focusSet['name']]);
        }

        // Check if any set has significant progress
        $bestSet = $topSets->sortByDesc('completion_percentage')->first();
        if ($bestSet && $bestSet->completion_percentage > 15) {
            return __('stats_insights.sets.progressing', ['set' => $bestSet->name]);
        }

        // Early stage collection
        return __('stats_insights.sets.early_stage');
    }

    /**
     * Identify the Focus Set from top sets
     * 
     * Focus Set criteria:
     * - Highest completion percentage
     * - AND relatively low total cards (< 200 preferred)
     * - Must have at least 10% completion
     *
     * @param Collection $topSets
     * @return array|null
     */
    public function identifyFocusSet(Collection $topSets): ?array
    {
        if ($topSets->isEmpty()) {
            return null;
        }

        // Filter sets with meaningful progress (>=10%) and manageable size (<= 200 cards)
        $candidates = $topSets->filter(function($set) {
            return $set->completion_percentage >= 10 && $set->total_in_set <= 200;
        });

        // If no candidates with strict criteria, relax the size constraint
        if ($candidates->isEmpty()) {
            $candidates = $topSets->filter(function($set) {
                return $set->completion_percentage >= 10;
            });
        }

        // Still no candidates? Return null
        if ($candidates->isEmpty()) {
            return null;
        }

        // Sort by completion percentage (highest first)
        $focusSet = $candidates->sortByDesc('completion_percentage')->first();

        return [
            'group_id' => $focusSet->group_id,
            'name' => $focusSet->name,
            'owned_count' => $focusSet->owned_count,
            'total_in_set' => $focusSet->total_in_set,
            'completion_percentage' => $focusSet->completion_percentage,
        ];
    }

    /**
     * Format condition name for display
     *
     * @param string|null $condition
     * @return string
     */
    public function formatCondition(?string $condition): string
    {
        if (!$condition) {
            return 'Unknown';
        }

        return ucwords(str_replace('_', ' ', $condition));
    }
}
