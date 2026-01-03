<?php

namespace App\Console\Commands;

use App\Models\PipelineRun;
use App\Models\Tcgdx\TcgdxCard;
use App\Models\Tcgdx\TcgdxSet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MapTcgdexToTcgcsvCommand extends Command
{
    protected $signature = 'tcgdex:map-to-tcgcsv 
                            {--dry-run : Show matches without saving}
                            {--sets-only : Map only sets, skip cards}';

    protected $description = 'Map TCGdex sets and cards to TCGCSV groups and products';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $setsOnly = $this->option('sets-only');
        
        // Start pipeline tracking
        $pipelineRun = PipelineRun::start('tcgdex:map', ['dry_run' => $dryRun]);
        
        $this->info('🔗 Mapping TCGdex to TCGCSV');
        $this->newLine();

        // Step 1: Map sets to groups
        $this->info('Step 1: Mapping sets to groups...');
        $setsMatched = $this->mapSetsToGroups($dryRun);
        
        $this->newLine();
        
        // Step 2: Map cards to products
        if (!$setsOnly) {
            $this->info('Step 2: Mapping cards to products...');
            $cardsMatched = $this->mapCardsToProducts($dryRun);
        } else {
            $this->info('⏭️  Skipping cards mapping (sets-only mode)');
            $cardsMatched = 0;
        }
        
        $this->newLine();
        $this->info('✅ Mapping completed!');
        $this->table(
            ['Type', 'Matched'],
            [
                ['Sets → Groups', $setsMatched],
                ['Cards → Products', $cardsMatched],
            ]
        );

        if ($dryRun) {
            $this->warn('This was a dry run. Use without --dry-run to save matches.');
        }

        // Mark pipeline run as success
        $pipelineRun->markSuccess([
            'rows_processed' => $setsMatched + $cardsMatched,
            'rows_updated' => $dryRun ? 0 : ($setsMatched + $cardsMatched),
        ]);

        return self::SUCCESS;
    }

    private function mapSetsToGroups(bool $dryRun): int
    {
        $tcgdxSets = TcgdxSet::all();
        $tcgcsvGroups = DB::table('tcgcsv_groups')->get();
        
        $matched = 0;

        foreach ($tcgdxSets as $set) {
            $setName = $set->name_en;
            
            if (empty($setName)) {
                continue;
            }
            
            // Try exact match first
            $group = $tcgcsvGroups->first(function($g) use ($setName) {
                return strtolower($g->name) === strtolower($setName);
            });

            // Try pattern match for modern sets (SWSH##, SV##, SM##, XY##)
            // TCGdex: "swsh7" → TCGCSV: "SWSH07: Evolving Skies"
            if (!$group && preg_match('/^(swsh|sv|sm|xy)(\d+)$/i', $set->tcgdex_id, $matches)) {
                $prefix = strtoupper($matches[1]);
                $number = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                $pattern = $prefix . $number . ':';
                
                $group = $tcgcsvGroups->first(function($g) use ($pattern) {
                    return str_starts_with($g->name, $pattern);
                });
            }

            // Try McDonald's pattern
            // TCGdex: "Macdonald's Collection 2019" → TCGCSV: "McDonald's Promos 2019"
            if (!$group && preg_match('/Macdonald\'?s Collection (\d{4})/i', $setName, $matches)) {
                $year = $matches[1];
                
                $group = $tcgcsvGroups->first(function($g) use ($year) {
                    return stripos($g->name, "McDonald's Promos $year") !== false;
                });
            }

            // Try Black Star Promos pattern
            // TCGdex: "Nintendo Black Star Promos" → TCGCSV: "Nintendo Promos"
            // TCGdex: "Wizards Black Star Promos" → TCGCSV: "Wizards Promos"
            // TCGdex: "SWSH Black Star Promos" (swshp) → TCGCSV: "SWSH: Sword & Shield Promo Cards"
            if (!$group && preg_match('/(Nintendo|Wizards) Black Star Promos/i', $setName, $matches)) {
                $prefix = $matches[1];
                
                $group = $tcgcsvGroups->first(function($g) use ($prefix) {
                    return stripos($g->name, "$prefix Promos") !== false;
                });
            }
            
            // Try modern Black Star Promos pattern (SWSH, SV, SM, XY, BW, HGSS, DP)
            // TCGdex: "SWSH Black Star Promos" (swshp) → TCGCSV: "SWSH: Sword & Shield Promo Cards"
            // TCGdex: "XY Black Star Promos" (xyp) → TCGCSV: "XY Promos"
            if (!$group && preg_match('/^(swsh|sv|sm|xy|bw|hgss|dp)p$/i', $set->tcgdex_id)) {
                $seriesPrefix = strtoupper(substr($set->tcgdex_id, 0, -1)); // Remove 'p'
                
                $group = $tcgcsvGroups->first(function($g) use ($seriesPrefix) {
                    // Try "SWSH: Sword & Shield Promo Cards" or "XY Promos" patterns
                    return (stripos($g->name, "$seriesPrefix:") !== false && stripos($g->name, "Promo") !== false)
                        || stripos($g->name, "$seriesPrefix Promos") !== false;
                });
            }

            // Try XY/SM/BW series pattern
            // TCGdex: "Flashfire" (xy2) → TCGCSV: "XY - Flashfire"
            // TCGdex: "Ultra Prism" (sm5) → TCGCSV: "SM - Ultra Prism"
            if (!$group && preg_match('/^(xy|sm|bw)(\d+)/i', $set->tcgdex_id, $matches)) {
                $seriesPrefix = strtoupper($matches[1]);
                
                $group = $tcgcsvGroups->first(function($g) use ($seriesPrefix, $setName) {
                    // Check for "XY - {setName}" or "SM - {setName}" pattern
                    return stripos($g->name, "$seriesPrefix - $setName") !== false;
                });
            }

            // Try Trainer Kit pattern
            // TCGdex: "BW trainer Kit (Zoroark)" (tk-bw-z) → TCGCSV: "BW Trainer Kit: Excadrill & Zoroark"
            // TCGdex: "HS trainer Kit (Gyarados)" (tk-hs-g) → TCGCSV: "HGSS Trainer Kit: Gyarados & Raichu"
            // TCGdex has separate sets for each pokemon, TCGCSV combines them
            if (!$group && preg_match('/^tk-(ex|dp|hs|bw|xy|sm)-/i', $set->tcgdex_id, $matches)) {
                $seriesPrefix = strtoupper($matches[1]);
                // Special case: HS in TCGdex = HGSS in TCGCSV
                if ($seriesPrefix === 'HS') {
                    $seriesPrefix = 'HGSS';
                }
                
                // Extract pokemon name from set name: "BW trainer Kit (Zoroark)" → "Zoroark"
                if (preg_match('/trainer Kit.*\(([^)]+)\)/i', $setName, $pokemonMatch)) {
                    $pokemon = $pokemonMatch[1];
                    
                    $group = $tcgcsvGroups->first(function($g) use ($seriesPrefix, $pokemon) {
                        // Check if group contains both series prefix and pokemon name
                        return stripos($g->name, "$seriesPrefix Trainer Kit") !== false 
                            && stripos($g->name, $pokemon) !== false;
                    });
                }
            }

            // Try fuzzy match
            if (!$group) {
                $group = $tcgcsvGroups->first(function($g) use ($setName) {
                    similar_text(strtolower($g->name), strtolower($setName), $percent);
                    return $percent > 85;
                });
            }

            if ($group) {
                $this->line("  ✓ {$set->tcgdex_id} ({$set->name_en}) → {$group->name}");
                
                if (!$dryRun) {
                    DB::table('tcgcsv_groups')
                        ->where('group_id', $group->group_id)
                        ->update(['tcgdex_set_id' => $set->tcgdex_id]);
                }
                
                $matched++;
            } else {
                $this->line("  ✗ No match for: {$setName}");
            }
        }

        return $matched;
    }

    private function mapCardsToProducts(bool $dryRun): int
    {
        // Only map cards from sets that are already mapped
        $mappedGroups = DB::table('tcgcsv_groups')
            ->whereNotNull('tcgdex_set_id')
            ->get();

        $matched = 0;
        $progressBar = $this->output->createProgressBar($mappedGroups->count());

        foreach ($mappedGroups as $group) {
            // Get TCGdex set to find its internal ID
            $tcgdxSet = TcgdxSet::where('tcgdex_id', $group->tcgdex_set_id)->first();
            if (!$tcgdxSet) {
                $progressBar->advance();
                continue;
            }
            
            $tcgdxCards = TcgdxCard::where('set_tcgdx_id', $tcgdxSet->id)->get();
            
            $tcgcsvProducts = DB::table('tcgcsv_products')
                ->where('group_id', $group->group_id)
                ->get();

            foreach ($tcgdxCards as $card) {
                $product = null;
                
                // STRATEGY 1: Exact name match + number match
                if ($card->name_en) {
                    $cardNameLower = strtolower($card->name_en);
                    
                    foreach ($tcgcsvProducts as $p) {
                        // Check exact name match first
                        if (strtolower($p->clean_name) !== $cardNameLower) {
                            continue;
                        }
                        
                        // Extract number before / (e.g., "015/025" -> "015")
                        $tcgcsvNumber = $p->card_number;
                        if (strpos($tcgcsvNumber, '/') !== false) {
                            $tcgcsvNumber = explode('/', $tcgcsvNumber)[0];
                        }
                        
                        // Normalize both numbers
                        $normalizedTcgcsv = $this->normalizeCardNumber($tcgcsvNumber);
                        $normalizedTcgdx = $this->normalizeCardNumber($card->number);
                        $normalizedLocalId = $this->normalizeCardNumber($card->local_id);
                        
                        // Match if numbers are equal
                        if ($normalizedTcgcsv === $normalizedTcgdx || $normalizedTcgcsv === $normalizedLocalId) {
                            $product = $p;
                            break;
                        }
                    }
                }
                
                // STRATEGY 2: Fuzzy name match (contains base name) + number match
                if (!$product && $card->name_en) {
                    $tcgdxNumber = $this->normalizeCardNumber($card->number);
                    $tcgdxLocalId = $this->normalizeCardNumber($card->local_id);
                    $cardNameLower = strtolower($card->name_en);
                    
                    // Clean the TCGdex name: remove common suffixes/prefixes for matching
                    $cleanName = preg_replace('/\s+(ex|gx|v|vmax|vstar|radiant|prime|break|\*)/i', '', $cardNameLower);
                    
                    foreach ($tcgcsvProducts as $p) {
                        // Extract number before /
                        $tcgcsvNumber = $p->card_number;
                        if (strpos($tcgcsvNumber, '/') !== false) {
                            $tcgcsvNumber = explode('/', $tcgcsvNumber)[0];
                        }
                        $pNumber = $this->normalizeCardNumber($tcgcsvNumber);
                        
                        // Numbers must match
                        if ($pNumber !== $tcgdxNumber && $pNumber !== $tcgdxLocalId) {
                            continue;
                        }
                        
                        // Check if TCGCSV name contains the TCGdex base name (or vice versa)
                        $pNameLower = strtolower($p->clean_name);
                        $pCleanName = preg_replace('/\s+(ex|gx|v|vmax|vstar|radiant|prime|break|holo|reverse holo|1st edition)/i', '', $pNameLower);
                        
                        // Remove parenthetical content from TCGCSV (e.g., "(Black Dot Error)")
                        $pCleanName = preg_replace('/\s*\([^)]*\)/', '', $pCleanName);
                        $pCleanName = trim($pCleanName);
                        
                        // Match if one name contains the other (after cleaning)
                        if (strpos($pCleanName, $cleanName) !== false || strpos($cleanName, $pCleanName) !== false) {
                            $product = $p;
                            break;
                        }
                    }
                }
                
                // STRATEGY 3: Number-only match (last resort, only if name is missing or very different)
                if (!$product) {
                    $tcgdxNumber = $this->normalizeCardNumber($card->number);
                    $tcgdxLocalId = $this->normalizeCardNumber($card->local_id);
                    
                    foreach ($tcgcsvProducts as $p) {
                        $tcgcsvNumber = $p->card_number;
                        if (strpos($tcgcsvNumber, '/') !== false) {
                            $tcgcsvNumber = explode('/', $tcgcsvNumber)[0];
                        }
                        $pNumber = $this->normalizeCardNumber($tcgcsvNumber);
                        
                        if ($pNumber === $tcgdxNumber || $pNumber === $tcgdxLocalId) {
                            $product = $p;
                            break;
                        }
                    }
                }

                if ($product) {
                    if (!$dryRun) {
                        DB::table('tcgcsv_products')
                            ->where('product_id', $product->product_id)
                            ->update(['tcgdex_card_id' => $card->tcgdex_id]);
                    }
                    
                    $matched++;
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        return $matched;
    }

    /**
     * Normalize card number for matching
     * Examples: "004/102" → "4", "4/102" → "4", "15A4" → "15A4", "SV04" → "SV4"
     */
    private function normalizeCardNumber(?string $number): string
    {
        if (empty($number)) {
            return '';
        }

        // Remove leading zeros while preserving letters and additional numbers
        // "004/102" → "4", "015" → "15", "15A4" → "15A4", "GG01" → "GG1"
        if (preg_match('/^([A-Z]*)0*(\d+[A-Z]?\d*)/', $number, $matches)) {
            $prefix = $matches[1] ?? '';
            $num = $matches[2];
            return $prefix . $num;
        }

        return $number;
    }
}
