<?php

use Laravel\Sanctum\Sanctum;

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    | Requests from these domains will receive stateful API authentication
    | cookies. Typically your local and production front-end domains.
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,localhost:8000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort()
    ))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    | This array contains the authentication guards that will be checked when
    | Sanctum is trying to authenticate an incoming request.
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    | This value controls the number of minutes until an issued token will be
    | considered expired. Null means tokens never expire.
    |
    | For VitalCare: tokens expire after 8 hours (480 minutes) for security.
    |
    */

    'expiration' => 480,

    /*
    |--------------------------------------------------------------------------
    | Token Prefix
    |--------------------------------------------------------------------------
    | Sanctum can prefix new tokens. Useful to visually identify Sanctum
    | tokens in logs or credential managers.
    |
    */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', 'vc_'),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    | Used to authenticate first-party SPA requests. These middleware will
    | be assigned to the 'api' middleware group automatically.
    |
    */

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies'      => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token'  => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],

];
