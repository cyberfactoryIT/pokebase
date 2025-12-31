<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TCGdex API Configuration
    |--------------------------------------------------------------------------
    |
    | TCGdex API base URL and connection settings
    | Docs: https://tcgdex.dev/docs
    |
    */

    'base_url' => env('TCGDX_BASE_URL', 'https://api.tcgdex.net/v2'),

    'timeout' => env('TCGDX_TIMEOUT', 30),

    'retry_count' => env('TCGDX_RETRY_COUNT', 3),

    'retry_sleep_ms' => env('TCGDX_RETRY_SLEEP_MS', 1000),
    
    /*
    |--------------------------------------------------------------------------
    | API Key (Optional)
    |--------------------------------------------------------------------------
    |
    | TCGdex is currently free and open source, but may require
    | authentication for premium features in the future.
    |
    */

    'api_key' => env('TCGDX_API_KEY'),
];
