#!/usr/bin/env php
<?php
/**
 * Script per verificare le statistiche di mapping delle carte
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "═══════════════════════════════════════════════════════════════\n";
echo "📊 STATISTICHE MAPPING CARTE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Statistiche RapidAPI Cards
$totalRapidCards = DB::table('rapidapi_cards')->where('game', 'pokemon')->count();
$mappedRapidCards = DB::table('rapidapi_cards')
    ->where('game', 'pokemon')
    ->whereNotNull('tcgcsv_product_id')
    ->count();
$percRapid = $totalRapidCards > 0 ? round(($mappedRapidCards / $totalRapidCards) * 100, 1) : 0;

echo "RapidAPI Cards → TCGCSV Products:\n";
echo "  Totale carte RapidAPI:     $totalRapidCards\n";
echo "  Mappate a TCGCSV:          $mappedRapidCards ($percRapid%)\n";
echo "  Non mappate:               " . ($totalRapidCards - $mappedRapidCards) . "\n";
echo "\n";

// Statistiche TCGdex Cards
$totalTcgdexCards = DB::table('tcgdx_cards')->count();
$mappedTcgdexCards = DB::table('tcgcsv_products')
    ->whereNotNull('tcgdex_card_id')
    ->count();
$percTcgdex = $totalTcgdexCards > 0 ? round(($mappedTcgdexCards / $totalTcgdexCards) * 100, 1) : 0;

echo "TCGdex Cards → TCGCSV Products:\n";
echo "  Totale carte TCGdex:       $totalTcgdexCards\n";
echo "  Mappate a TCGCSV:          $mappedTcgdexCards ($percTcgdex%)\n";
echo "  Non mappate:               " . ($totalTcgdexCards - $mappedTcgdexCards) . "\n";
echo "\n";

// Breakdown per set mappato
$setStats = DB::table('tcgcsv_groups as g')
    ->leftJoin('tcgcsv_products as p', 'g.group_id', '=', 'p.group_id')
    ->leftJoin('rapidapi_cards as rc', 'p.product_id', '=', 'rc.tcgcsv_product_id')
    ->where('g.category_id', 3)
    ->whereNotNull('g.rapidapi_episode_id')
    ->select(
        'g.name',
        'g.abbreviation',
        DB::raw('COUNT(DISTINCT p.product_id) as total_products'),
        DB::raw('COUNT(DISTINCT rc.card_id) as mapped_cards')
    )
    ->groupBy('g.group_id', 'g.name', 'g.abbreviation')
    ->having(DB::raw('COUNT(DISTINCT p.product_id)'), '>', 0)
    ->orderBy('mapped_cards', 'desc')
    ->limit(15)
    ->get();

echo "Top 15 Set con più carte mappate (RapidAPI):\n";
echo str_repeat('─', 80) . "\n";
printf("%-45s | %-8s | Products | Mapped | %%\n", 'Set', 'Abbr');
echo str_repeat('─', 80) . "\n";
foreach ($setStats as $stat) {
    $perc = $stat->total_products > 0 ? round(($stat->mapped_cards / $stat->total_products) * 100, 1) : 0;
    printf("%-45s | %-8s | %8d | %6d | %5.1f%%\n", 
        substr($stat->name, 0, 45), 
        $stat->abbreviation ?? 'N/A', 
        $stat->total_products,
        $stat->mapped_cards,
        $perc
    );
}

// Set senza nessuna carta mappata
$unmappedSets = DB::table('tcgcsv_groups as g')
    ->leftJoin('tcgcsv_products as p', 'g.group_id', '=', 'p.group_id')
    ->leftJoin('rapidapi_cards as rc', 'p.product_id', '=', 'rc.tcgcsv_product_id')
    ->where('g.category_id', 3)
    ->whereNotNull('g.rapidapi_episode_id')
    ->select(
        'g.name',
        'g.abbreviation',
        'g.rapidapi_episode_id',
        DB::raw('COUNT(DISTINCT p.product_id) as total_products'),
        DB::raw('COUNT(DISTINCT rc.card_id) as mapped_cards')
    )
    ->groupBy('g.group_id', 'g.name', 'g.abbreviation', 'g.rapidapi_episode_id')
    ->having(DB::raw('COUNT(DISTINCT p.product_id)'), '>', 0)
    ->having(DB::raw('COUNT(DISTINCT rc.card_id)'), '=', 0)
    ->orderBy('total_products', 'desc')
    ->limit(10)
    ->get();

if ($unmappedSets->isNotEmpty()) {
    echo "\n";
    echo "Set mappati ma senza carte mappate (top 10):\n";
    echo str_repeat('─', 80) . "\n";
    printf("%-45s | %-8s | Products | RapidAPI ID\n", 'Set', 'Abbr');
    echo str_repeat('─', 80) . "\n";
    foreach ($unmappedSets as $set) {
        printf("%-45s | %-8s | %8d | %11d\n", 
            substr($set->name, 0, 45), 
            $set->abbreviation ?? 'N/A', 
            $set->total_products,
            $set->rapidapi_episode_id
        );
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "💡 ANALISI:\n";
echo "═══════════════════════════════════════════════════════════════\n";

if ($percRapid < 50) {
    echo "⚠️  La copertura delle carte RapidAPI è bassa ($percRapid%)\n";
    echo "   Possibili cause:\n";
    echo "   • Molti set mappati ma carte non ancora sincronizzate\n";
    echo "   • Differenze nei nomi delle carte tra i sistemi\n";
    echo "   • Necessità di eseguire il comando di mapping delle carte\n";
} elseif ($percRapid < 80) {
    echo "⚡ La copertura delle carte RapidAPI è discreta ($percRapid%)\n";
    echo "   Continua a migliorare i mapping dei set per aumentarla\n";
} else {
    echo "✅ La copertura delle carte RapidAPI è ottima ($percRapid%)!\n";
}

echo "\n";
echo "Per migliorare il mapping delle carte:\n";
echo "  1. Verifica che i set siano mappati: ./check_unmapped_sets.php\n";
echo "  2. Esegui il mapping automatico: php artisan rapidapi:map-cards\n";
echo "  3. Per card specifiche: ./find_mapping_match.php \"nome carta\"\n";
echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
