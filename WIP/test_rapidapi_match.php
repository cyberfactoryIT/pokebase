#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Try to match some unmapped TCGdex sets with RapidAPI episodes
$unmappedSets = ['xy1', 'xy2', 'xy3', 'xy4', 'sm1', 'sm5', 'sm8', 'sm9', 'ecard1'];

echo "Testing TCGdex → RapidAPI matching:" . PHP_EOL . PHP_EOL;

foreach ($unmappedSets as $tcgdexId) {
    $tcgdxSet = DB::table('tcgdx_sets')->where('tcgdex_id', $tcgdexId)->first();
    if (!$tcgdxSet) continue;
    
    $name = json_decode($tcgdxSet->name)->en ?? '';
    echo "TCGdex: $tcgdexId = $name" . PHP_EOL;
    
    // Try exact match
    $episode = DB::table('rapidapi_episodes')
        ->whereRaw('LOWER(name) = ?', [strtolower($name)])
        ->first(['episode_id', 'name', 'slug', 'code']);
    
    if ($episode) {
        echo "  ✓ EXACT: Episode {$episode->episode_id} | {$episode->name} | {$episode->slug}" . PHP_EOL;
    } else {
        // Try fuzzy match
        $episode = DB::table('rapidapi_episodes')
            ->where('name', 'LIKE', '%' . $name . '%')
            ->first(['episode_id', 'name', 'slug', 'code']);
        
        if ($episode) {
            echo "  ~ PARTIAL: Episode {$episode->episode_id} | {$episode->name} | {$episode->slug}" . PHP_EOL;
        } else {
            echo "  ✗ Not found in RapidAPI" . PHP_EOL;
        }
    }
    echo PHP_EOL;
}
