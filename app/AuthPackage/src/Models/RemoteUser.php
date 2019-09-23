<?php


namespace VATSIMUK\Auth\Remote\Models;

use VATSIMUK\Auth\Remote\GraphQL\Builder;
use VATSIMUK\Auth\Remote\RemoteModel;

class RemoteUser extends RemoteModel
{
    protected static $unguarded = true;
    protected static $singleMethod = "user";
    protected static $manyMethod = "users";

    protected static $defaultFields = [
        "name_first",
        "name_last",
    ];

    public static function findWithAccessToken($token, $fields = null)
    {
        $query = new Builder('authUser', static::generateParams($fields));
        $response = $query->execute($token);
        return !$response->isEmpty() ? static::initModelWithData($response->getResults()) : null;
    }
}
