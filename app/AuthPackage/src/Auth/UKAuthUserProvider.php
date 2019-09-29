<?php


namespace VATSIMUK\Auth\Remote\Auth;


use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use VATSIMUK\Auth\Remote\Models\RemoteUser;

class UKAuthUserProvider implements UserProvider
{
    private $model;

    /**
     * Create a new UK Auth user provider.
     *
     * @return void
     */
    public function __construct(RemoteUser $userModel)
    {
        $this->model = $userModel;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param mixed $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        return config('ukauth.auth_user_model')::find($identifier);
    }

    public function retrieveByToken($identifier, $token){
        return config('ukauth.auth_user_model')::findWithAccessToken($token);
    }

    public function updateRememberToken(Authenticatable $user, $token){}
    public function retrieveByCredentials(array $credentials){}

    /**
     * Validate a user against the given credentials.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // TODO: Implement validateCredentials() method.
    }
}
