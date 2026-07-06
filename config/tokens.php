<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Access Token Time-To-Live
    |--------------------------------------------------------------------------
    |
    | Lifetime, in minutes, of the OAuth2 access tokens issued at login. The
    | challenge requires a 30-minute expiration.
    |
    */

    'access_token_ttl' => (int) env('ACCESS_TOKEN_TTL_MINUTES', 30),

];
