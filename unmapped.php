#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$unmapped = DB::table('tcgdx_sets as t')
    ->leftJoin('tcgcsv_groups as g', 't.tcgdex_id', '=', 'g.tcgdex_set_id')
    ->whereNull('g.tcgdex_set_id')
    ->select('t.tcgdex_id', 't.name', 't.card_count_total')
    ->orderBy('t.release_date', 'desc')
    ->get()
    ->filter(function($s) {
        $id = $s->tcgdex_id;
        // Exclude Pocket sets
        return !preg_match('/^[AB]\d+[a-z]*$/', $id) 
            && !preg_match('/^me\d+$/', $id) 
            && $id !== 'mep' 
            && $id !== 'P-A'
            && !preg_match('/^sv10\.5[wb]$/', $id);
    });

echo "📊 TCGdex Sets NON Mappati (esclusi Pocket): " . $unmapped->count() . PHP_EOL . PHP_EOL;

foreach ($unmapped as $set) {
    $name = json_decode($set->name)->en ?? 'Unknown';
    printf("%-20s | %-45s | %3d cards\n", $set->tcgdex_id, $name, $set->card_count_total);
}

echo PHP_EOL . "Breakdown:" . PHP_EOL;
$promos = $unmapped->filter(fn($s) => str_contains(strtolower(json_decode($s->name)->en ?? ""), "promo"));
$trainers = $unmapped->filter(fn($s) => str_contains(strtolower(json_decode($s->name)->en ?? ""), "trainer kit"));
$mcdonalds = $unmapped->filter(fn($s) => str_contains(strtolower(json_decode($s->name)->en ?? ""), "macdonald"));
$others = $unmapped->count() - $promos->count() - $trainers->count() - $mcdonalds->count();
echo "  Promos: " . $promos->count() . PHP_EOL;
echo "  Trainer Kits: " . $trainers->count() . PHP_EOL;
echo "  McDonalds: " . $mcdonalds->count() . PHP_EOL;
echo "  Others: " . $others . PHP_EOL;
