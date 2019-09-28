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
