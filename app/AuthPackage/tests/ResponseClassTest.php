<?php

namespace VATSIMUK\Auth\Tests;

use Orchestra\Testbench\TestCase;
use VATSIMUK\Auth\Remote\GraphQL\Builder as PackageBuilder;
use VATSIMUK\Auth\Remote\GraphQL\Response as PackageResponse;
use VATSIMUK\Auth\Tests\Fixtures\MockJsonResponse;

class ResponseClassTest extends TestCase
{
    protected $validResponse;
    protected $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new PackageBuilder('user', ["name_first", "name_last",]);

        $this->validResponse = new PackageResponse(MockJsonResponse::successfulResponse(), $this->builder);
    }

    /** @test */
    public function itCorrectlyDetectsWhenErrorsAreInTheResponse()
    {
        $this->assertFalse($this->validResponse->hasErrors());
    }
}
