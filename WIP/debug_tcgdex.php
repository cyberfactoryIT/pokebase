<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pdo = DB::connection()->getPdo();
$userId = 6;

echo "TCGDEX collection for user $userId:" . PHP_EOL;

$result = $pdo->query("
    SELECT 
        uc.id,
        uc.tcgdex_card_id,
        uc.quantity,
        uc.cached_price,
        uc.cached_price_currency,
        tc.name,
        tc.price_eur
    FROM user_collection uc
    LEFT JOIN tcgdx_cards tc ON uc.tcgdex_card_id = tc.id
    WHERE uc.user_id = $userId
    AND uc.tcgdex_card_id IS NOT NULL
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($result as $row) {
    $name = json_decode($row['name'], true);
    $displayName = $name['en'] ?? 'Unknown';
    echo "  {$displayName}: qty={$row['quantity']}, cached_price={$row['cached_price']} {$row['cached_price_currency']}, price_eur={$row['price_eur']}" . PHP_EOL;
}

$total = $pdo->query("
    SELECT SUM(cached_price * quantity) as total
    FROM user_collection
    WHERE user_id = $userId
    AND tcgdex_card_id IS NOT NULL
    AND cached_price IS NOT NULL
")->fetch(PDO::FETCH_ASSOC);

echo PHP_EOL . "Total TCGDEX value: €" . number_format($total['total'] ?: 0, 2) . PHP_EOL;
