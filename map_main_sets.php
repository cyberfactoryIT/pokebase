#!/usr/bin/env php
<?php
/**
 * Script per mappare manualmente i set principali SWSH/SM/SV
 * che il sistema automatico non riesce a trovare
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "═══════════════════════════════════════════════════════════════\n";
echo "🔧 MAPPING MANUALE SET PRINCIPALI\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Mapping manuale per i set principali non mappati
$mappings = [
    ['rapid_id' => 24, 'rapid_name' => 'Silver Tempest', 'tcgcsv_abbr' => 'SWSH12'],
    ['rapid_id' => 25, 'rapid_name' => 'Silver Tempest Trainer Gallery', 'tcgcsv_abbr' => 'SWSH12: TG'],
    ['rapid_id' => 26, 'rapid_name' => 'Lost Origin', 'tcgcsv_abbr' => 'SWSH11'],
    ['rapid_id' => 27, 'rapid_name' => 'Lost Origin Trainer Gallery', 'tcgcsv_abbr' => 'SWSH11: TG'],
    ['rapid_id' => 30, 'rapid_name' => 'Astral Radiance', 'tcgcsv_abbr' => 'SWSH10'],
    ['rapid_id' => 31, 'rapid_name' => 'Astral Radiance Trainer Gallery', 'tcgcsv_abbr' => 'SWSH10:TG'],
    ['rapid_id' => 32, 'rapid_name' => 'Brilliant Stars', 'tcgcsv_abbr' => 'SWSH09'],
    ['rapid_id' => 33, 'rapid_name' => 'Brilliant Stars Trainer Gallery', 'tcgcsv_abbr' => 'SWSH09:TG'],
    ['rapid_id' => 34, 'rapid_name' => 'Fusion Strike', 'tcgcsv_abbr' => 'SWSH08'],
    ['rapid_id' => 35, 'rapid_name' => 'Celebrations', 'tcgcsv_abbr' => 'CLB'],
    ['rapid_id' => 36, 'rapid_name' => 'Celebrations: Classic Collection', 'tcgcsv_abbr' => 'CCC'],
    ['rapid_id' => 37, 'rapid_name' => 'Evolving Skies', 'tcgcsv_abbr' => 'SWSH07'],
    ['rapid_id' => 38, 'rapid_name' => 'Chilling Reign', 'tcgcsv_abbr' => 'SWSH06'],
    ['rapid_id' => 41, 'rapid_name' => 'Shining Fates Shiny Vault', 'tcgcsv_abbr' => 'SHFSV'],
    ['rapid_id' => 43, 'rapid_name' => 'Vivid Voltage', 'tcgcsv_abbr' => 'SWSH04'],
    ['rapid_id' => 44, 'rapid_name' => 'Champion\'s Path', 'tcgcsv_abbr' => 'CHP'],
    ['rapid_id' => 46, 'rapid_name' => 'Darkness Ablaze', 'tcgcsv_abbr' => 'SWSH03'],
    ['rapid_id' => 47, 'rapid_name' => 'Rebel Clash', 'tcgcsv_abbr' => 'SWSH02'],
    ['rapid_id' => 48, 'rapid_name' => 'Sword & Shield', 'tcgcsv_abbr' => 'SWSH01'],
    ['rapid_id' => 49, 'rapid_name' => 'SWSH Black Star Promos', 'tcgcsv_abbr' => 'SWSD'],
    ['rapid_id' => 50, 'rapid_name' => 'Cosmic Eclipse', 'tcgcsv_abbr' => 'SM12'],
    ['rapid_id' => 54, 'rapid_name' => 'Unified Minds', 'tcgcsv_abbr' => 'SM11'],
    ['rapid_id' => 55, 'rapid_name' => 'Unbroken Bonds', 'tcgcsv_abbr' => 'SM10'],
    ['rapid_id' => 56, 'rapid_name' => 'Detective Pikachu', 'tcgcsv_abbr' => 'DEP'],
    ['rapid_id' => 57, 'rapid_name' => 'Team Up', 'tcgcsv_abbr' => 'SM9'],
    ['rapid_id' => 58, 'rapid_name' => 'Lost Thunder', 'tcgcsv_abbr' => 'SM8'],
    ['rapid_id' => 62, 'rapid_name' => 'Forbidden Light', 'tcgcsv_abbr' => 'SM06'],
    ['rapid_id' => 63, 'rapid_name' => 'Ultra Prism', 'tcgcsv_abbr' => 'SM05'],
    ['rapid_id' => 65, 'rapid_name' => 'Crimson Invasion', 'tcgcsv_abbr' => 'SM04'],
    ['rapid_id' => 66, 'rapid_name' => 'Shining Legends', 'tcgcsv_abbr' => 'SHL'],
    ['rapid_id' => 67, 'rapid_name' => 'Burning Shadows', 'tcgcsv_abbr' => 'SM03'],
    ['rapid_id' => 68, 'rapid_name' => 'Guardians Rising', 'tcgcsv_abbr' => 'SM02'],
    ['rapid_id' => 69, 'rapid_name' => 'Sun & Moon', 'tcgcsv_abbr' => 'SM01'],
    ['rapid_id' => 70, 'rapid_name' => 'SM Black Star Promos', 'tcgcsv_abbr' => 'SMP'],
    ['rapid_id' => 23, 'rapid_name' => 'SV Black Star Promos', 'tcgcsv_abbr' => 'SVP'],
    ['rapid_id' => 22, 'rapid_name' => 'Crown Zenith Galarian Gallery', 'tcgcsv_abbr' => 'CRZ:GG'],
    ['rapid_id' => 167, 'rapid_name' => 'Base Set 2', 'tcgcsv_abbr' => 'BS2'],
];

$updated = 0;
$failed = 0;
$skipped = 0;

foreach ($mappings as $map) {
    $group = DB::table('tcgcsv_groups')
        ->where('abbreviation', $map['tcgcsv_abbr'])
        ->where('category_id', 3)
        ->first();
    
    if ($group) {
        // Check if already mapped
        if ($group->rapidapi_episode_id) {
            $skipped++;
            echo "⏭  Già mappato: {$map['rapid_name']}\n";
            continue;
        }
        
        $episode = DB::table('rapidapi_episodes')
            ->where('episode_id', $map['rapid_id'])
            ->first();
        
        if ($episode) {
            DB::table('tcgcsv_groups')
                ->where('group_id', $group->group_id)
                ->update([
                    'rapidapi_episode_id' => $episode->episode_id,
                    'logo_url' => $episode->logo_url,
                    'updated_at' => now(),
                ]);
            
            $updated++;
            echo "✓ Mappato: {$map['rapid_name']} → {$group->name}\n";
        } else {
            $failed++;
            echo "✗ RapidAPI Episode non trovato: {$map['rapid_name']} (ID: {$map['rapid_id']})\n";
        }
    } else {
        $failed++;
        echo "✗ TCGCSV Group non trovato: {$map['rapid_name']} (abbr: {$map['tcgcsv_abbr']})\n";
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "📊 RIEPILOGO:\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Mappati:        $updated\n";
echo "Già mappati:    $skipped\n";
echo "Falliti:        $failed\n";
echo "Totale:         " . count($mappings) . "\n";
echo "\n";

if ($updated > 0) {
    echo "✅ Mapping completato! Esegui ./check_unmapped_sets.php per vedere le statistiche aggiornate.\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
