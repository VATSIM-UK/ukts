<?php

namespace VATSIMUK\Auth\Tests\Fixtures;

class MockJsonResponse
{
    public static function successfulResponse()
    {
        return json_decode('{"data":{"user":{"id":"1300005","name_first":"5th","name_last":"Test"}}}"');
    }
}
