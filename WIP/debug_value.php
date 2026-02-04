<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pdo = DB::connection()->getPdo();
$userId = 6;

$result = $pdo->query("
    SELECT 
        SUM(cached_price * quantity) as total_eur,
        COUNT(*) as items_with_price,
        SUM(quantity) as total_quantity
    FROM user_collection 
    WHERE user_id = $userId 
    AND cached_price IS NOT NULL
")->fetch(PDO::FETCH_ASSOC);

echo "Collection value for user $userId:" . PHP_EOL;
echo "  Total EUR: €" . number_format($result['total_eur'], 2) . PHP_EOL;
echo "  Items with price: " . $result['items_with_price'] . PHP_EOL;
echo "  Total cards: " . $result['total_quantity'] . PHP_EOL;
