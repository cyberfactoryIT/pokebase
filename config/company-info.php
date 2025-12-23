<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Supported countries.
    |--------------------------------------------------------------------------
    |
    */

    'countries' => [
        'dk' => [
            'provider' => env('COMPANY_INFO_PROVIDER_DK', 'virk'),
        ],
        'gb' => [
            'provider' => env('COMPANY_INFO_PROVIDER_GB', 'gazette'),
        ],
        'no' => [
            'provider' => env('COMPANY_INFO_PROVIDER_NO', 'cvrapi'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default country.
    |--------------------------------------------------------------------------
    |
    */

    'default_country' => env('COMPANY_INFO_DEFAULT_COUNTRY', 'dk'),

    /*
    |--------------------------------------------------------------------------
    | Active provider (key from 'providers')
    |--------------------------------------------------------------------------
    |
    | Select which configured provider to use by default. Can be overridden
    | per-environment via .env (COMPANY_INFO_ACTIVE_PROVIDER).
    |
    */
    'active_provider' => env('COMPANY_INFO_ACTIVE_PROVIDER', env('COMPANY_INFO_PROVIDER_DK', 'cvrapi')),

    /*
    |--------------------------------------------------------------------------
    | Company information service providers.
    |--------------------------------------------------------------------------
    |
    */

    'providers' => [
        'cvrapi' => [
            'base_url'   => env('COMPANY_INFO_CVRAPI_BASE_URL', 'https://cvrapi.dk/api'),
            // Path appended to base_url when searching; leaving empty will use base_url?search=...
            'search_path' => env('COMPANY_INFO_CVRAPI_SEARCH_PATH', ''),
            // query param used for the CVR value
            'query_param' => env('COMPANY_INFO_CVRAPI_QUERY_PARAM', 'search'),
            // Optional headers for the HTTP request
            'headers' => [
                'User-Agent' => env('COMPANY_INFO_CVRAPI_USER_AGENT', 'Basecard/1.0'),
                'Accept' => 'application/json',
            ],
        ],
        'gazette' => [
            'base_url' => env('COMPANY_INFO_GAZETTE_BASE_URL', 'https://api.companieshouse.gov.uk'),
            'key'      => env('COMPANY_INFO_GAZETTE_KEY', ''),
        ],
        'virk' => [
            'base_url' => env('COMPANY_INFO_VIRK_BASE_URL', 'http://distribution.virk.dk'),
            'user_id'  => env('COMPANY_INFO_VIRK_USER_ID', ''),
            'password' => env('COMPANY_INFO_VIRK_PASSWORD', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Maximum number of results returned.
    |--------------------------------------------------------------------------
    |
    */

    'max_results' => env('COMPANY_INFO_MAX_RESULTS', 10),
];
