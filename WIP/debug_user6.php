<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pdo = DB::connection()->getPdo();
$userId = 6;

echo "User $userId collection:" . PHP_EOL;
echo "  Total items: " . $pdo->query("SELECT COUNT(*) FROM user_collection WHERE user_id = $userId")->fetchColumn() . PHP_EOL;
echo "  TCGDEX items: " . $pdo->query("SELECT COUNT(*) FROM user_collection WHERE user_id = $userId AND tcgdex_card_id IS NOT NULL")->fetchColumn() . PHP_EOL;
echo "  TCGCSV items: " . $pdo->query("SELECT COUNT(*) FROM user_collection WHERE user_id = $userId AND product_id IS NOT NULL")->fetchColumn() . PHP_EOL;
echo "  With cached_price: " . $pdo->query("SELECT COUNT(*) FROM user_collection WHERE user_id = $userId AND cached_price IS NOT NULL")->fetchColumn() . PHP_EOL;
echo PHP_EOL;
echo "Joinable TCGDEX: " . $pdo->query("SELECT COUNT(*) FROM user_collection uc JOIN tcgdx_cards tc ON uc.tcgdex_card_id = tc.id WHERE uc.user_id = $userId AND tc.price_eur IS NOT NULL")->fetchColumn() . PHP_EOL;
echo "Joinable TCGCSV: " . $pdo->query("SELECT COUNT(*) FROM user_collection uc JOIN tcgcsv_products tp ON uc.product_id = tp.product_id WHERE uc.user_id = $userId AND tp.cardmarket_price_eur IS NOT NULL")->fetchColumn() . PHP_EOL;
