<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Organizations feature toggle
    |--------------------------------------------------------------------------
    |
    | When disabled (false) the application will hide organization-related
    | UI and fallback to user name where appropriate. Keep DB tables and
    | migrations untouched so feature can be re-enabled later.
    |
    */
    'enabled' => (bool) env('ORGANIZATIONS_ENABLED', false),
];
