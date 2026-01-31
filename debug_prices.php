<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pdo = DB::connection()->getPdo();

echo "Collection items: " . $pdo->query('SELECT COUNT(*) FROM user_collection WHERE user_id = 1')->fetchColumn() . PHP_EOL;
echo "TCGDEX items: " . $pdo->query('SELECT COUNT(*) FROM user_collection WHERE user_id = 1 AND tcgdex_card_id IS NOT NULL')->fetchColumn() . PHP_EOL;
echo "TCGCSV items: " . $pdo->query('SELECT COUNT(*) FROM user_collection WHERE user_id = 1 AND product_id IS NOT NULL')->fetchColumn() . PHP_EOL;
echo "With cached_price: " . $pdo->query('SELECT COUNT(*) FROM user_collection WHERE user_id = 1 AND cached_price IS NOT NULL')->fetchColumn() . PHP_EOL;
echo PHP_EOL;
echo "TCGDEX cards with price_eur: " . $pdo->query('SELECT COUNT(*) FROM tcgdx_cards WHERE price_eur IS NOT NULL')->fetchColumn() . PHP_EOL;
echo "TCGCSV products with cardmarket_price_eur: " . $pdo->query('SELECT COUNT(*) FROM tcgcsv_products WHERE cardmarket_price_eur IS NOT NULL')->fetchColumn() . PHP_EOL;
echo PHP_EOL;
echo "Join test TCGDEX: " . $pdo->query('SELECT COUNT(*) FROM user_collection uc JOIN tcgdx_cards tc ON uc.tcgdex_card_id = tc.id WHERE uc.user_id = 1 AND tc.price_eur IS NOT NULL')->fetchColumn() . PHP_EOL;
echo "Join test TCGCSV: " . $pdo->query('SELECT COUNT(*) FROM user_collection uc JOIN tcgcsv_products tp ON uc.product_id = tp.product_id WHERE uc.user_id = 1 AND tp.cardmarket_price_eur IS NOT NULL')->fetchColumn() . PHP_EOL;
