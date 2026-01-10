#!/usr/bin/env php
<?php
/**
 * Script di ricerca rapida per trovare potenziali match tra sistemi
 * Utile per debugging e per mappare manualmente set difficili
 * 
 * Uso: ./find_mapping_match.php "Nome Set"
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

if ($argc < 2) {
    echo "Uso: ./find_mapping_match.php \"Nome Set\"\n";
    echo "Esempio: ./find_mapping_match.php \"Evolving Skies\"\n";
    exit(1);
}

$searchTerm = $argv[1];

echo "═══════════════════════════════════════════════════════════════\n";
echo "🔍 RICERCA MAPPING PER: '{$searchTerm}'\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// ==============================================================================
// Cerca in TCGCSV Groups
// ==============================================================================

echo "📦 TCGCSV Groups:\n";
echo "─────────────────────────────────────────────────────────────\n";

$tcgcsvResults = DB::table('tcgcsv_groups')
    ->where('category_id', 3)
    ->where(function($q) use ($searchTerm) {
        $q->where('name', 'LIKE', "%{$searchTerm}%")
          ->orWhere('abbreviation', 'LIKE', "%{$searchTerm}%");
    })
    ->limit(10)
    ->get();

if ($tcgcsvResults->isEmpty()) {
    echo "❌ Nessun risultato\n\n";
} else {
    foreach ($tcgcsvResults as $group) {
        echo sprintf(
            "  ID: %-6s | %-45s | Abbr: %-8s\n",
            $group->group_id,
            substr($group->name, 0, 45),
            $group->abbreviation ?? 'N/A'
        );
        echo sprintf(
            "             RapidAPI: %-6s | TCGdex: %-10s | Logo: %s\n",
            $group->rapidapi_episode_id ?? '❌',
            $group->tcgdex_set_id ?? '❌',
            $group->logo_url ? '✓' : '❌'
        );
        echo "\n";
    }
}

// ==============================================================================
// Cerca in RapidAPI Episodes
// ==============================================================================

echo "🚀 RapidAPI Episodes:\n";
echo "─────────────────────────────────────────────────────────────\n";

$rapidResults = DB::table('rapidapi_episodes')
    ->where('game', 'pokemon')
    ->where(function($q) use ($searchTerm) {
        $q->where('name', 'LIKE', "%{$searchTerm}%")
          ->orWhere('slug', 'LIKE', "%{$searchTerm}%")
          ->orWhere('code', 'LIKE', "%{$searchTerm}%");
    })
    ->limit(10)
    ->get();

if ($rapidResults->isEmpty()) {
    echo "❌ Nessun risultato\n\n";
} else {
    foreach ($rapidResults as $episode) {
        echo sprintf(
            "  ID: %-6s | %-40s | Code: %-8s\n",
            $episode->episode_id,
            substr($episode->name, 0, 40),
            $episode->code ?? 'N/A'
        );
        echo sprintf(
            "             Slug: %-30s | Logo: %s\n",
            substr($episode->slug ?? '', 0, 30),
            $episode->logo_url ? '✓' : '❌'
        );
        
        // Check if already mapped
        $mapped = DB::table('tcgcsv_groups')
            ->where('rapidapi_episode_id', $episode->episode_id)
            ->first(['group_id', 'name']);
        
        if ($mapped) {
            echo sprintf(
                "             ✅ Mappato a TCGCSV: [%s] %s\n",
                $mapped->group_id,
                substr($mapped->name, 0, 40)
            );
        } else {
            echo "             ⚠️  Non mappato a TCGCSV\n";
        }
        echo "\n";
    }
}

// ==============================================================================
// Cerca in TCGdex Sets
// ==============================================================================

echo "🎴 TCGdex Sets:\n";
echo "─────────────────────────────────────────────────────────────\n";

$tcgdexResults = DB::table('tcgdx_sets')
    ->where(function($q) use ($searchTerm) {
        $q->where('name', 'LIKE', "%{$searchTerm}%")
          ->orWhere('tcgdex_id', 'LIKE', "%{$searchTerm}%");
    })
    ->limit(10)
    ->get();

if ($tcgdexResults->isEmpty()) {
    echo "❌ Nessun risultato\n\n";
} else {
    foreach ($tcgdexResults as $set) {
        $name = json_decode($set->name)->en ?? 'Unknown';
        echo sprintf(
            "  ID: %-12s | %-45s | Cards: %3d\n",
            $set->tcgdex_id,
            substr($name, 0, 45),
            $set->card_count_total ?? 0
        );
        
        // Check if already mapped
        $mapped = DB::table('tcgcsv_groups')
            ->where('tcgdex_set_id', $set->tcgdex_id)
            ->first(['group_id', 'name']);
        
        if ($mapped) {
            echo sprintf(
                "                   ✅ Mappato a TCGCSV: [%s] %s\n",
                $mapped->group_id,
                substr($mapped->name, 0, 40)
            );
        } else {
            echo "                   ⚠️  Non mappato a TCGCSV\n";
        }
        echo "\n";
    }
}

// ==============================================================================
// Suggerimenti per il mapping
// ==============================================================================

echo "═══════════════════════════════════════════════════════════════\n";
echo "💡 SUGGERIMENTI:\n";
echo "═══════════════════════════════════════════════════════════════\n";

if (!$tcgcsvResults->isEmpty() && !$rapidResults->isEmpty()) {
    $tcgcsv = $tcgcsvResults->first();
    $rapid = $rapidResults->first();
    
    echo "\nPer mappare TCGCSV → RapidAPI:\n";
    echo "  UPDATE tcgcsv_groups \n";
    echo "  SET rapidapi_episode_id = {$rapid->episode_id},\n";
    echo "      logo_url = '{$rapid->logo_url}'\n";
    echo "  WHERE group_id = {$tcgcsv->group_id};\n";
}

if (!$tcgcsvResults->isEmpty() && !$tcgdexResults->isEmpty()) {
    $tcgcsv = $tcgcsvResults->first();
    $tcgdex = $tcgdexResults->first();
    
    echo "\nPer mappare TCGCSV → TCGdex:\n";
    echo "  UPDATE tcgcsv_groups \n";
    echo "  SET tcgdex_set_id = '{$tcgdex->tcgdex_id}'\n";
    echo "  WHERE group_id = {$tcgcsv->group_id};\n";
}

echo "\nPer mappare automaticamente:\n";
echo "  php artisan rapidapi:map-episodes --dry-run\n";
echo "  php artisan tcgdex:map-to-tcgcsv --dry-run\n";
echo "\n";

echo "═══════════════════════════════════════════════════════════════\n";
