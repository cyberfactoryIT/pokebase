<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cardmarket ETL Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration handles the ETL pipeline for importing Cardmarket
    | product catalogues and price guides. All imports are idempotent and
    | maintain historical price snapshots.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | File Download URLs
    |--------------------------------------------------------------------------
    |
    | URLs to download the Cardmarket product catalogue and price guide files.
    | These are JSON files hosted on S3.
    |
    | URL Pattern: https://downloads.s3.cardmarket.com/productCatalog/productList/products_singles_{game_id}.json
    | Examples:
    |   - products_singles_1.json (Magic: The Gathering)
    |   - products_singles_3.json (Yu-Gi-Oh!)
    |   - products_singles_6.json (Pokémon)
    |
    */

    'games' => [
        'pokemon' => [
            'id' => 6,
            'name' => 'Pokémon',
            'products_url' => env('CARDMARKET_POKEMON_PRODUCTS_URL', 'https://downloads.s3.cardmarket.com/productCatalog/productList/products_singles_6.json'),
            'prices_url' => env('CARDMARKET_POKEMON_PRICES_URL', 'https://downloads.s3.cardmarket.com/productCatalog/priceGuide/price_guide_6.json'),
        ],
        'mtg' => [
            'id' => 1,
            'name' => 'Magic: The Gathering',
            'products_url' => env('CARDMARKET_MTG_PRODUCTS_URL', 'https://downloads.s3.cardmarket.com/productCatalog/productList/products_singles_1.json'),
            'prices_url' => env('CARDMARKET_MTG_PRICES_URL', 'https://downloads.s3.cardmarket.com/productCatalog/priceGuide/price_guide_1.json'),
        ],
        'yugioh' => [
            'id' => 3,
            'name' => 'Yu-Gi-Oh!',
            'products_url' => env('CARDMARKET_YUGIOH_PRODUCTS_URL', 'https://downloads.s3.cardmarket.com/productCatalog/productList/products_singles_3.json'),
            'prices_url' => env('CARDMARKET_YUGIOH_PRICES_URL', 'https://downloads.s3.cardmarket.com/productCatalog/priceGuide/price_guide_3.json'),
        ],
    ],

    // Default game to import
    'default_game' => env('CARDMARKET_DEFAULT_GAME', 'pokemon'),

    /*
    |--------------------------------------------------------------------------
    | Storage Paths
    |--------------------------------------------------------------------------
    |
    | Local storage paths for raw downloads and archived files.
    | All paths are relative to storage/app/
    |
    */

    'storage' => [
        'raw' => 'cardmarket/raw',           // Downloaded JSON files
        'archive' => 'cardmarket/archive',    // Old files (optional cleanup)
    ],

    /*
    |--------------------------------------------------------------------------
    | Import Options
    |--------------------------------------------------------------------------
    |
    | Default settings for the ETL pipeline.
    |
    */

    'import' => [
        // Chunk size for batch DB operations
        'chunk_size' => env('CARDMARKET_CHUNK_SIZE', 500),

        // Progress reporting interval (log every N chunks)
        'progress_interval' => env('CARDMARKET_PROGRESS_INTERVAL', 10),

        // Default timezone for as_of_date
        'timezone' => env('CARDMARKET_TIMEZONE', 'Europe/Copenhagen'),

        // Default currency
        'default_currency' => env('CARDMARKET_CURRENCY', 'EUR'),

        // Skip download if file exists and is recent (hours)
        'cache_duration' => env('CARDMARKET_CACHE_HOURS', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | CSV Parsing Options
    |--------------------------------------------------------------------------
    |
    | JSON parsing options.
    |
    */

    'json' => [
        // Maximum depth for JSON parsing
        'max_depth' => 512,
        
        // JSON decode options
        'options' => JSON_THROW_ON_ERROR,
    ],

    /*
    |-JSON Field Mapping
    |--------------------------------------------------------------------------
    |
    | Map JSON field names to database columns.
    |
    | Products JSON structure:
    | {
    |   "version": 1,
    |   "createdAt": "2024-12-27T10:00:00Z",
    |   "products": [
    |     {
    |       "idProduct": 123456,
    |       "name": "Charizard",
    |       "idCategory": 6,
    |       "categoryName": "Pokémon Singles",
    |       "idExpansion": 789,
    |       "idMetacard": 101112,
    |       "dateAdded": "2024-01-15"
    |     }
    |   ]
    | }
    |
    | Prices JSON structure:
    | {
    |   "version": 1,
    |   "createdAt": "2024-12-27T10:00:00Z",
    |   "priceGuides": [
    |     {
    |       "idProduct": 123456,
    |       "idCategory": 6,
    |       "avg": 45.99,
    |       "low": 30.00,
    |       "trend": 50.00,
    |       "avg-holo": 120.00,
    |       "low-holo": 90.00,
    |       "trend-holo": 130.00,
    |       "avg1": 46.50,
    |       "avg7": 47.20,
    |       "avg30": 44.80
    |     }
    |   ]
    | }
    |
    */

    'mapping' => [
        'products' => [
            'cardmarket_product_id' => 'idProduct',
            'name' => 'name',
            'id_category' => 'idCategory',
            'category_name' => 'categoryName',
            'id_expansion' => 'idExpansion',
            'id_metacard' => 'idMetacard',
            'date_added' => 'dateAdded',
        ],
        'prices' => [
            'cardmarket_product_id' => 'idProduct',
            'id_category' => 'idCategory',
            'avg' => 'avg',
            'low' => 'low',
            'trend' => 'trend',
            'avg_holo' => 'avg-holo',
            'low_holo' => 'low-holo',
            'trend_holo' => 'trend-holo',
            'avg1' => 'avg1',
            'avg7' => 'avg7',
            'avg30' => 'avg30',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Queue settings for async processing.
    |
    */

    'queue' => [
        'connection' => env('CARDMARKET_QUEUE_CONNECTION', 'database'),
        'name' => env('CARDMARKET_QUEUE_NAME', 'cardmarket'),
        'timeout' => env('CARDMARKET_QUEUE_TIMEOUT', 3600), // 1 hour
        'tries' => env('CARDMARKET_QUEUE_TRIES', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Log channel and verbosity settings.
    |
    */

    'logging' => [
        'channel' => env('CARDMARKET_LOG_CHANNEL', 'cardmarket'),
        'level' => env('CARDMARKET_LOG_LEVEL', 'info'),
    ],

];
