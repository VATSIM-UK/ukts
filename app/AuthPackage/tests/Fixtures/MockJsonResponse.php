<?php

namespace VATSIMUK\Auth\Tests\Fixtures;

class MockJsonResponse
{
    /**
     * Represents a successfully retrieved user.
     *
     * @return mixed
     */
    public static function successfulResponse()
    {
        return json_decode('{"data":{"user":{"id":"1300005","name_first":"5th","name_last":"Test"}}}');
    }

    /**
     * Mock response for when a user is not found.
     *
     * @return mixed
     */
    public static function emptyResponse()
    {
        return json_decode('{"data":{"user":null}}');
    }

    public static function erroredResponse()
    {
        return json_decode('{"errors": [ { "message": "There was an error" } ]}');
    }
}
