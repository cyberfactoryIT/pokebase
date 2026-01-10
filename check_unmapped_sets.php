#!/usr/bin/env php
<?php
/**
 * Script per individuare le estensioni NON mappate tra TCGCSV e RapidAPI
 * 
 * Questo script mostra:
 * 1. Set TCGCSV senza mapping a RapidAPI Episode
 * 2. Set RapidAPI senza mapping a TCGCSV Group
 * 3. Set TCGCSV senza mapping a TCGdex
 * 4. Suggerimenti per il mapping manuale
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "═══════════════════════════════════════════════════════════════\n";
echo "🔍 ANALISI ESTENSIONI NON MAPPATE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// ==============================================================================
// 1. TCGCSV Groups senza RapidAPI Episode
// ==============================================================================

echo "1️⃣  TCGCSV GROUPS senza RapidAPI Episode mapping:\n";
echo "─────────────────────────────────────────────────────────────\n";

$tcgcsvUnmapped = DB::table('tcgcsv_groups')
    ->whereNull('rapidapi_episode_id')
    ->where('category_id', 3) // Pokemon only
    ->orderBy('published_on', 'desc')
    ->get();

echo "📊 Totale: " . $tcgcsvUnmapped->count() . " gruppi non mappati\n\n";

if ($tcgcsvUnmapped->isEmpty()) {
    echo "✅ Tutti i gruppi TCGCSV sono mappati a RapidAPI!\n\n";
} else {
    $table = [];
    foreach ($tcgcsvUnmapped as $group) {
        $table[] = [
            'ID' => $group->group_id,
            'Name' => substr($group->name, 0, 40),
            'Abbr' => $group->abbreviation ?? 'N/A',
            'Published' => substr($group->published_on ?? '', 0, 10),
            'TCGdex' => $group->tcgdex_set_id ?? '❌',
        ];
    }
    
    // Print table
    printf("%-6s | %-40s | %-8s | %-10s | %-10s\n", 
        'ID', 'Name', 'Abbr', 'Published', 'TCGdex');
    echo str_repeat('─', 90) . "\n";
    
    foreach ($table as $row) {
        printf("%-6s | %-40s | %-8s | %-10s | %-10s\n", 
            $row['ID'], $row['Name'], $row['Abbr'], $row['Published'], $row['TCGdex']);
    }
    echo "\n";
}

// ==============================================================================
// 2. RapidAPI Episodes senza TCGCSV Group
// ==============================================================================

echo "2️⃣  RapidAPI EPISODES senza TCGCSV Group mapping:\n";
echo "─────────────────────────────────────────────────────────────\n";

$rapidApiUnmapped = DB::table('rapidapi_episodes as re')
    ->leftJoin('tcgcsv_groups as tg', 're.episode_id', '=', 'tg.rapidapi_episode_id')
    ->whereNull('tg.rapidapi_episode_id')
    ->where('re.game', 'pokemon')
    ->select('re.episode_id', 're.name', 're.code', 're.slug', 're.logo_url')
    ->orderBy('re.episode_id', 'desc')
    ->get();

echo "📊 Totale: " . $rapidApiUnmapped->count() . " episodi non mappati\n\n";

if ($rapidApiUnmapped->isEmpty()) {
    echo "✅ Tutti gli episodi RapidAPI sono mappati a TCGCSV!\n\n";
} else {
    $table = [];
    foreach ($rapidApiUnmapped as $episode) {
        $table[] = [
            'ID' => $episode->episode_id,
            'Name' => substr($episode->name, 0, 35),
            'Code' => $episode->code ?? 'N/A',
            'Slug' => substr($episode->slug ?? '', 0, 20),
            'Logo' => $episode->logo_url ? '✓' : '❌',
        ];
    }
    
    // Print table
    printf("%-6s | %-35s | %-8s | %-20s | %-4s\n", 
        'ID', 'Name', 'Code', 'Slug', 'Logo');
    echo str_repeat('─', 80) . "\n";
    
    foreach ($table as $row) {
        printf("%-6s | %-35s | %-8s | %-20s | %-4s\n", 
            $row['ID'], $row['Name'], $row['Code'], $row['Slug'], $row['Logo']);
    }
    echo "\n";
}

// ==============================================================================
// 3. TCGCSV Groups senza TCGdex Set
// ==============================================================================

echo "3️⃣  TCGCSV GROUPS senza TCGdex Set mapping:\n";
echo "─────────────────────────────────────────────────────────────\n";

$tcgcsvNoTcgdex = DB::table('tcgcsv_groups')
    ->whereNull('tcgdex_set_id')
    ->where('category_id', 3) // Pokemon only
    ->orderBy('published_on', 'desc')
    ->get();

echo "📊 Totale: " . $tcgcsvNoTcgdex->count() . " gruppi non mappati a TCGdex\n\n";

if ($tcgcsvNoTcgdex->isEmpty()) {
    echo "✅ Tutti i gruppi TCGCSV sono mappati a TCGdex!\n\n";
} else {
    $table = [];
    foreach ($tcgcsvNoTcgdex->take(20) as $group) {
        $table[] = [
            'ID' => $group->group_id,
            'Name' => substr($group->name, 0, 45),
            'Abbr' => $group->abbreviation ?? 'N/A',
            'RapidAPI' => $group->rapidapi_episode_id ? '✓' : '❌',
        ];
    }
    
    // Print table
    printf("%-6s | %-45s | %-8s | %-8s\n", 
        'ID', 'Name', 'Abbr', 'RapidAPI');
    echo str_repeat('─', 75) . "\n";
    
    foreach ($table as $row) {
        printf("%-6s | %-45s | %-8s | %-8s\n", 
            $row['ID'], $row['Name'], $row['Abbr'], $row['RapidAPI']);
    }
    
    if ($tcgcsvNoTcgdex->count() > 20) {
        echo "\n... e altri " . ($tcgcsvNoTcgdex->count() - 20) . " gruppi\n";
    }
    echo "\n";
}

// ==============================================================================
// 4. Statistiche di Mapping
// ==============================================================================

echo "4️⃣  STATISTICHE COMPLESSIVE:\n";
echo "─────────────────────────────────────────────────────────────\n";

$totalTcgcsv = DB::table('tcgcsv_groups')->where('category_id', 3)->count();
$totalRapidApi = DB::table('rapidapi_episodes')->where('game', 'pokemon')->count();
$totalTcgdex = DB::table('tcgdx_sets')->count();

$mappedToRapidapi = DB::table('tcgcsv_groups')
    ->where('category_id', 3)
    ->whereNotNull('rapidapi_episode_id')
    ->count();

$mappedToTcgdex = DB::table('tcgcsv_groups')
    ->where('category_id', 3)
    ->whereNotNull('tcgdex_set_id')
    ->count();

$fullyMapped = DB::table('tcgcsv_groups')
    ->where('category_id', 3)
    ->whereNotNull('rapidapi_episode_id')
    ->whereNotNull('tcgdex_set_id')
    ->count();

$percRapidapi = $totalTcgcsv > 0 ? round(($mappedToRapidapi / $totalTcgcsv) * 100, 1) : 0;
$percTcgdex = $totalTcgcsv > 0 ? round(($mappedToTcgdex / $totalTcgcsv) * 100, 1) : 0;
$percFully = $totalTcgcsv > 0 ? round(($fullyMapped / $totalTcgcsv) * 100, 1) : 0;

echo "TCGCSV Groups (Pokemon):        {$totalTcgcsv}\n";
echo "RapidAPI Episodes (Pokemon):    {$totalRapidApi}\n";
echo "TCGdex Sets:                    {$totalTcgdex}\n";
echo "\n";
echo "TCGCSV → RapidAPI:              {$mappedToRapidapi}/{$totalTcgcsv} ({$percRapidapi}%)\n";
echo "TCGCSV → TCGdex:                {$mappedToTcgdex}/{$totalTcgcsv} ({$percTcgdex}%)\n";
echo "Completamente mappati:          {$fullyMapped}/{$totalTcgcsv} ({$percFully}%)\n";
echo "\n";

// ==============================================================================
// 5. Suggerimenti per il mapping
// ==============================================================================

echo "5️⃣  SUGGERIMENTI:\n";
echo "─────────────────────────────────────────────────────────────\n";
echo "Per mappare RapidAPI → TCGCSV:\n";
echo "  php artisan rapidapi:map-episodes\n";
echo "  php artisan rapidapi:map-episodes --dry-run (per testare)\n";
echo "\n";
echo "Per mappare TCGdex → TCGCSV:\n";
echo "  php artisan tcgdex:map-to-tcgcsv\n";
echo "  php artisan tcgdex:map-to-tcgcsv --dry-run (per testare)\n";
echo "  php artisan tcgdex:map-to-tcgcsv --sets-only (solo set, no carte)\n";
echo "\n";
echo "Per vedere i dettagli di un mapping specifico:\n";
echo "  ./test_rapidapi_match.php\n";
echo "  ./unmapped.php (per vedere TCGdex non mappati)\n";
echo "\n";
echo "Per mappare manualmente:\n";
echo "  UPDATE tcgcsv_groups SET rapidapi_episode_id = [ID] WHERE group_id = [ID];\n";
echo "  UPDATE tcgcsv_groups SET tcgdex_set_id = '[tcgdex_id]' WHERE group_id = [ID];\n";
echo "\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ Analisi completata!\n";
echo "═══════════════════════════════════════════════════════════════\n";
