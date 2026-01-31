<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pdo = DB::connection()->getPdo();

echo "Users in database:" . PHP_EOL;
$users = $pdo->query('SELECT id, name, email FROM users LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $user) {
    $count = $pdo->query("SELECT COUNT(*) FROM user_collection WHERE user_id = {$user['id']}")->fetchColumn();
    echo "  ID {$user['id']}: {$user['name']} ({$user['email']}) - {$count} cards" . PHP_EOL;
}
