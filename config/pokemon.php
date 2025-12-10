<?php

return [
    'api_key' => env('POKEMON_API_KEY'),
    'base_url' => env('POKEMON_API_BASE_URL', 'https://api.pokemontcg.io/v2'),
    'page_size' => 250, // massimo supportato dall'API
];
