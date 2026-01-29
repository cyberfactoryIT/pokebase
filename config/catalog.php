<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Catalog Backend
    |--------------------------------------------------------------------------
    |
    | This value determines which backend to use for the Pokemon catalog.
    | 
    | Supported: "tcgcsv", "tcgdex"
    |
    | IMPORTANT: TCGDEX is ONLY available for Pokemon (game_id = 1).
    | For all other games (Magic, YuGiOh, etc.), TCGCSV will be used regardless
    | of this setting.
    |
    */
    'backend' => env('CATALOG_BACKEND', 'tcgcsv'),

    /*
    |--------------------------------------------------------------------------
    | Experimental Features
    |--------------------------------------------------------------------------
    |
    | Enable experimental TCGDEX features for testing
    |
    */
    'experimental' => env('CATALOG_EXPERIMENTAL', false),
    
    /*
    |--------------------------------------------------------------------------
    | Supported Games for TCGDEX
    |--------------------------------------------------------------------------
    |
    | Game IDs that support TCGDEX backend
    |
    */
    'tcgdex_supported_games' => [
        1, // Pokemon
    ],
];
