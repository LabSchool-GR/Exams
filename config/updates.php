<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Update Center
    |--------------------------------------------------------------------------
    |
    | The in-app update center is intentionally read-only. It can display the
    | current version, check the latest published GitHub release, show release
    | notes, and expose a package download link for administrators.
    |
    */

    'enabled' => env('APP_UPDATE_ENABLED', true),

    'manifest' => [
        'url' => env('APP_UPDATE_MANIFEST_URL'),
    ],

    'github' => [
        'repository' => env('APP_UPDATE_GITHUB_REPOSITORY'),
        'api_base_url' => env('APP_UPDATE_GITHUB_API_URL', 'https://api.github.com'),
        'timeout_seconds' => env('APP_UPDATE_TIMEOUT_SECONDS', 5),
        'cache_ttl_minutes' => env('APP_UPDATE_CACHE_TTL_MINUTES', 30),
    ],

];
