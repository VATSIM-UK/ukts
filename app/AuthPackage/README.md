# VATSIM UK CAS Laravel Package

## About
This package provides the boilerplate implementation to quickly and easily interface with the VATSIM UK Central Authentication Service (CAS). It allows for Eloquent-esque relationships and niceties to be maintained, whilst still keeping each service separate from each other.

## Installation
Once installed via `composer require vatsimuk/auth-package`, simply reference the `VATSIMUK\Auth\Remote\Models\RemoteUser` model in Eloquent relations just as you would any other relationship.

Currently:
1. The front-end should call `http://myapp.test/auth/login` to retrieve the OAuth authorisation URL. The client should be redirected there, and the front-end should remember the intended url for after login is complete.
2. After login, the user will be redirected to `http://myapp.test/auth/login/complete` with an authorisation token in the `token` query string. This token should be passed as via Bearer in the HTTP Authorization header. You should setup a route on this path to handle the post-login event.

You must define the following environment variables in the `.env` file to ensure the API can be reached:
* `AUTH_ROOT` - The root of the UK Auth application
* `AUTH_CLIENT_ID` & `AUTH_CLIENT_SECRET` - The Applications Client ID and Secret from the Auth Application (i.e. to access authenticated user's data). Make this client with `php artisan passport:client --name="Example Microservice"` in the Auth service. Callback should route to the service this package is being installed to. Replace myapp.test with the appropriate path - `http://myapp.test/auth/login/verify`
* `AUTH_MACHINE_CLIENT_ID` & `AUTH_MACHINE_CLIENT_SECRET` - The Applications Client ID and Secret from the Auth Application, to be used for Machine-Machine authentication (i.e. large scale user data collection). **Can't** be the same as the above set of credentials. Make this client with `php artisan passport:client --client --name="Machine-Machine ClientCred Grant"` in the Auth service.

### Authentication Setup
After login via the SSO as above, the user is returned to `/auth/login/complete`, along with the JWT `token` in the query string. This should be passed as a bearer token with each request to the API to authorise the user's request. Middleware and Authentication guards are built into the package, so that you can use `Auth::user()` and `Auth::check()` as normal. To install:

1. In `config/auth.php`, set the default guard to `ukauth`
    ```php
    'defaults' => [
        'guard' => 'ukauth',
        'passwords' => 'users',
    ],
    ```
 
2. In `config/auth.php`, add in the `ukauth` guard as shown below:
    ```php
    'guards' => [
        
        ... Default Guards Here ...
    
        'ukauth' => [
            'driver' => 'jwt',
            'provider' => 'ukauth',
        ],
    ],
    ```
   
It's as simple as that! You can use the standard `auth` route middleware to protect routes.

Note that by default, the `Auth` facade will return instances of `VATSIMUK\Auth\Remote\Models\RemoteUser`. If you want to leverage your own extension of the RemoteUser model with relationships and such, create a `ukauth.php` config file like so:
```php
<?php

return [
  'auth_user_model' => \App\User::class
];
```
