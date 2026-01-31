<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CardMarket API Configuration (via RapidAPI)
    |--------------------------------------------------------------------------
    |
    | Configuration for CardMarket API integration via RapidAPI.
    | Supports: Lorcana, One Piece (and potentially other TCGs)
    |
    | API Documentation: https://rapidapi.com/tcggopro/api/cardmarket-api-tcg
    |
    */

    'base_url' => env('CMAPI_BASE_URL', 'https://cardmarket-api-tcg.p.rapidapi.com'),

    'timeout' => env('CMAPI_TIMEOUT', 30),

    'retry_count' => env('CMAPI_RETRY_COUNT', 3),

    'retry_sleep_ms' => env('CMAPI_RETRY_SLEEP_MS', 1000),

    /*
    |--------------------------------------------------------------------------
    | RapidAPI Authentication
    |--------------------------------------------------------------------------
    |
    | Required headers for RapidAPI authentication
    |
    */

    'rapidapi_key' => env('CMAPI_RAPIDAPI_KEY'),

    'rapidapi_host' => env('CMAPI_RAPIDAPI_HOST', 'cardmarket-api-tcg.p.rapidapi.com'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limits depend on your RapidAPI subscription tier:
    | - Basic (Free): 100 req/day, 30 req/min
    | - Pro ($9.90/mo): 3,000 req/day, 300 req/min
    | - Ultra ($24.90/mo): 15,000 req/day, 300 req/min
    | - Mega ($49.50/mo): 50,000 req/day, 600 req/min
    |
    */

    'rate_limit_per_minute' => env('CMAPI_RATE_LIMIT_PER_MINUTE', 30),

];
