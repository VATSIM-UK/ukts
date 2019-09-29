<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Auth User Model
    |--------------------------------------------------------------------------
    |
    | Here you may specify the class instance that is returned for authenticated
    | users.
    |
    */

    'auth_user_model' => VATSIMUK\Auth\Remote\Models\RemoteUser::class,

    /*
    |--------------------------------------------------------------------------
    | Auth Service URL
    |--------------------------------------------------------------------------
    |
    | Here you may specify the location of the auth service. This must be the
    | root domain, such that the oauth methods can be accessed, as well as the
    | api / graphql routes.
    |
    */
    'root_url' => env('AUTH_ROOT', 'https://auth.vatsim.uk'),

    /*
    |--------------------------------------------------------------------------
    | GraphQL Endpoint
    |--------------------------------------------------------------------------
    |
    | This is the path to the GraphQL api endpoint, including forward slash.
    |
    */
    'graphql_path' => '/api',

    /*
    |--------------------------------------------------------------------------
    | OAuth 2.0 Endpoint
    |--------------------------------------------------------------------------
    |
    | This is the path to the OAuth 2.0 server, including forward slash.
    |
    */
    'oauth_path' => '/oauth',

    /*
    |--------------------------------------------------------------------------
    | OAuth 2.0 Client Details
    |--------------------------------------------------------------------------
    */

    /*
     * The OAuth Client ID for User Auth API calls.
     */
    'client_id' => env('AUTH_CLIENT_ID'),

    /*
     * The OAuth Client secret for User Auth API calls.
     */
    'client_secret' => env('AUTH_CLIENT_SECRET'),

    /*
     * The OAuth Client ID for machine-machine API calls.
     */
    'machine_client_id' => env('AUTH_MACHINE_CLIENT_ID'),

    /*
     * The OAuth Client secret for machine-machine API calls.
     */
    'machine_client_secret' => env('AUTH_MACHINE_CLIENT_SECRET'),

];
