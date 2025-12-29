<?php

return [

    /*
    |--------------------------------------------------------------------------
    | RapidAPI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for RapidAPI Cardmarket TCG integration
    |
    */

    'cardmarket' => [
        'enabled' => env('RAPIDAPI_CARDMARKET_ENABLED', true),
        'api_key' => env('RAPIDAPI_KEY', '4549717005msh02dfff5f9c87208p1a081fjsnb6ed6ac3cc89'),
        'host' => env('RAPIDAPI_CARDMARKET_HOST', 'cardmarket-api-tcg.p.rapidapi.com'),
        'base_url' => env('RAPIDAPI_CARDMARKET_BASE_URL', 'https://cardmarket-api-tcg.p.rapidapi.com'),
        
        // Rate limiting
        'rate_limit' => [
            'requests_per_minute' => env('RAPIDAPI_RATE_LIMIT', 50),
            'delay_ms' => 1200, // Delay between requests in milliseconds
        ],

        // Endpoints
        'endpoints' => [
            'pokemon_cards' => '/pokemon/cards',
            'mtg_cards' => '/magic/cards',
            'yugioh_cards' => '/yugioh/cards',
        ],

        // Pagination
        'per_page' => 20, // API default
    ],

];
