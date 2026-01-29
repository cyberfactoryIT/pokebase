<?php

/**
 * Populate price_eur and price_usd fields in tcgdx_cards from raw JSON data
 * 
 * Usage: php populate_tcgdx_prices.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tcgdx\TcgdxCard;

echo "Starting to populate price fields in tcgdx_cards...\n\n";

$batchSize = 500;
$totalCards = TcgdxCard::count();
$updated = 0;
$skipped = 0;

echo "Total cards to process: {$totalCards}\n\n";

TcgdxCard::whereNotNull('raw')
    ->chunkById($batchSize, function ($cards) use (&$updated, &$skipped) {
        foreach ($cards as $card) {
            $priceEur = null;
            $priceUsd = null;
            
            $raw = $card->raw;
            
            // Extract EUR price from Cardmarket
            if (isset($raw['pricing']['cardmarket'])) {
                $cm = $raw['pricing']['cardmarket'];
                
                // Priority 1: trend price
                if (!empty($cm['trend']) && $cm['trend'] > 0) {
                    $priceEur = (float) $cm['trend'];
                }
                // Priority 2: average price
                elseif (!empty($cm['avg']) && $cm['avg'] > 0) {
                    $priceEur = (float) $cm['avg'];
                }
                // Priority 3: avg30 price
                elseif (!empty($cm['avg30']) && $cm['avg30'] > 0) {
                    $priceEur = (float) $cm['avg30'];
                }
            }
            
            // Extract USD price from TCGPlayer
            if (isset($raw['pricing']['tcgplayer'])) {
                $tcp = $raw['pricing']['tcgplayer'];
                
                // Try to find the best variant price
                $variants = [];
                
                // Check holofoil variant
                if (isset($tcp['holofoil'])) {
                    $variants[] = $tcp['holofoil'];
                }
                
                // Check normal variant
                if (isset($tcp['normal'])) {
                    $variants[] = $tcp['normal'];
                }
                
                // Check reverse holofoil variant
                if (isset($tcp['reverseHolofoil'])) {
                    $variants[] = $tcp['reverseHolofoil'];
                }
                
                // Check 1st edition variants
                if (isset($tcp['1st-edition-holofoil'])) {
                    $variants[] = $tcp['1st-edition-holofoil'];
                }
                if (isset($tcp['1st-edition-normal'])) {
                    $variants[] = $tcp['1st-edition-normal'];
                }
                
                // Find best price from available variants
                foreach ($variants as $variant) {
                    // Priority 1: marketPrice
                    if (!empty($variant['marketPrice']) && $variant['marketPrice'] > 0) {
                        $priceUsd = (float) $variant['marketPrice'];
                        break;
                    }
                    // Priority 2: midPrice
                    elseif (!empty($variant['midPrice']) && $variant['midPrice'] > 0 && !$priceUsd) {
                        $priceUsd = (float) $variant['midPrice'];
                    }
                    // Priority 3: lowPrice
                    elseif (!empty($variant['lowPrice']) && $variant['lowPrice'] > 0 && !$priceUsd) {
                        $priceUsd = (float) $variant['lowPrice'];
                    }
                }
            }
            
            // Update card if we found at least one price
            if ($priceEur !== null || $priceUsd !== null) {
                $card->update([
                    'price_eur' => $priceEur,
                    'price_usd' => $priceUsd,
                ]);
                $updated++;
                
                if ($updated % 100 === 0) {
                    echo "Processed {$updated} cards...\n";
                }
            } else {
                $skipped++;
            }
        }
    });

echo "\n";
echo "✅ Completed!\n";
echo "   Updated: {$updated} cards\n";
echo "   Skipped: {$skipped} cards (no pricing data)\n";
echo "   Total: " . ($updated + $skipped) . " cards\n";
