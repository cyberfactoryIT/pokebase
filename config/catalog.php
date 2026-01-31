<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Catalog Backend (DEPRECATED)
    |--------------------------------------------------------------------------
    |
    | ⚠️ DEPRECATED: This config is no longer used.
    | The catalog backend is now configured per-game in the 'games' table.
    | 
    | Each game has a 'catalog_backend' column:
    | - Pokemon: 'tcgdex'
    | - Yu-Gi-Oh: 'tcgcsv'
    | - Magic: 'tcgcsv'
    | - etc.
    |
    | To change a game's backend, update the 'games' table directly.
    |
    */
    'backend' => env('CATALOG_BACKEND', 'tcgcsv'), // DEPRECATED - not used anymore

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
    | Supported Games for TCGDEX (DEPRECATED)
    |--------------------------------------------------------------------------
    |
    | ⚠️ DEPRECATED: This config is no longer used.
    | The backend is now defined per-game in the database.
    |
    */
    'tcgdex_supported_games' => [
        1, // Pokemon (DEPRECATED - check games.catalog_backend instead)
    ],
];
