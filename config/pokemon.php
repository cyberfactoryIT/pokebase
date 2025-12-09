<?php

return [
    'api_key' => env('POKEMON_TCG_API_KEY'),
    'base_url' => env('POKEMON_TCG_API_BASE', 'https://api.pokemontcg.io/v2'),
    'page_size' => 250, // massimo supportato dall'API
];
