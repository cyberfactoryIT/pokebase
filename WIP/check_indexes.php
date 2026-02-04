<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check counts by game
echo "=== Groups by game ===\n";
$pokemonCount = DB::table('tcgcsv_groups')->where('game_id', 1)->count();
$mtgCount = DB::table('tcgcsv_groups')->where('game_id', 2)->count();
$totalCount = DB::table('tcgcsv_groups')->count();

echo "Pokemon (game_id=1): {$pokemonCount}\n";
echo "MTG (game_id=2): {$mtgCount}\n";
echo "Total: {$totalCount}\n\n";

// Show some examples
echo "=== Sample records ===\n";
$samples = DB::table('tcgcsv_groups')
    ->select('id', 'group_id', 'category_id', 'game_id', 'name')
    ->whereIn('group_id', [1, 2, 3])
    ->orderBy('category_id')
    ->orderBy('group_id')
    ->get();

foreach ($samples as $s) {
    echo "ID:{$s->id} group_id:{$s->group_id} cat:{$s->category_id} game:{$s->game_id} - {$s->name}\n";
}
