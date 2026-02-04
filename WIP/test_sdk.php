<?php

require 'vendor/autoload.php';

Pokemon\Pokemon::ApiKey('1fec095e-b12d-4c16-b1a7-17d96a10a75c');

try {
    $cards = Pokemon\Pokemon::Card()->where(['set.id' => 'det1'])->page(1)->pageSize(2)->all();
    echo 'Type: ' . gettype($cards) . PHP_EOL;
    echo 'Count: ' . count($cards) . PHP_EOL;
    if (count($cards) > 0) {
        echo 'First card class: ' . get_class($cards[0]) . PHP_EOL;
        echo 'First card methods: ' . implode(', ', get_class_methods($cards[0])) . PHP_EOL;
        print_r($cards[0]);
    }
    
    echo PHP_EOL . 'Pagination test:' . PHP_EOL;
    $pagination = Pokemon\Pokemon::Card()->where(['set.id' => 'det1'])->page(1)->pageSize(2)->pagination();
    echo 'Pagination class: ' . get_class($pagination) . PHP_EOL;
    echo 'Methods: ' . implode(', ', get_class_methods($pagination)) . PHP_EOL;
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
