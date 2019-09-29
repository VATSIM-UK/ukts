<?php


namespace VATSIMUK\Auth\Remote\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use VATSIMUK\Auth\Remote\GraphQL\Builder;
use VATSIMUK\Auth\Remote\RemoteModel;

class RemoteUser extends RemoteModel implements Authenticatable
{
    protected static $unguarded = true;
    protected static $singleMethod = "user";
    protected static $manyMethod = "users";

    protected static $defaultFields = [
        "name_first",
        "name_last",
    ];

    public static function findWithAccessToken($token, $columns = null)
    {
        $query = new Builder('authUser', static::generateParams($columns));
        $response = $query->execute($token);
        return !$response->isEmpty() ? static::initModelWithData($response->getResults()) : null;
    }

    public function fresh($columns = null)
    {
        if($this->auth_token){
            return static::findWithAccessToken($this->auth_token, $columns);
        }
        return parent::fresh($columns);
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return "id";
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return null;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param string $value
     * @return void
     */
    public function setRememberToken($value)
    {
        return null;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return "remember_token";
    }
}
