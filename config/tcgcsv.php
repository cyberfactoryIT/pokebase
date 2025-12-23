<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TCGCSV API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for importing Pokemon TCG data from tcgcsv.com
    | This is separate from the pokemontcg.io pipeline.
    |
    */

    'base_url' => env('TCGCSV_BASE_URL', 'https://tcgcsv.com/tcgplayer'),
    
    'category_id' => env('TCGCSV_CATEGORY_ID', 3), // Pokemon category
    
    'timeout' => env('TCGCSV_TIMEOUT', 30), // seconds
    
    'retry' => [
        'times' => env('TCGCSV_RETRY_TIMES', 3),
        'sleep' => env('TCGCSV_RETRY_SLEEP', 2000), // milliseconds
        'backoff_multiplier' => 2,
    ],
    
    'chunk_size' => env('TCGCSV_CHUNK_SIZE', 50),
];
