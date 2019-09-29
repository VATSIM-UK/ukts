<?php

namespace VATSIMUK\Auth\Remote\Tests;

use Orchestra\Testbench\TestCase;
use VATSIMUK\Auth\Remote\GraphQL\Builder as PackageBuilder;
use VATSIMUK\Auth\Remote\GraphQL\Response as PackageResponse;
use VATSIMUK\Auth\Tests\Fixtures\MockJsonResponse;

class ResponseClassTest extends TestCase
{
    protected $builder;
    protected $normalResponse;
    protected $erroredResponse;
    protected $emptyDataResponse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new PackageBuilder('user', ["name_first", "name_last",]);
        $this->erroredResponse = new PackageResponse(MockJsonResponse::erroredResponse(), $this->builder);
        $this->normalResponse = new PackageResponse(MockJsonResponse::successfulResponse(), $this->builder);
        $this->emptyDataResponse = new PackageResponse(MockJsonResponse::emptyResponse(), $this->builder);
    }

    /** @test */
    public function itCorrectlyDetectsWhenErrorsAreInTheResponse()
    {
        $this->assertFalse($this->normalResponse->hasErrors());
        $this->assertTrue($this->erroredResponse->hasErrors());
    }

    /** @test */
    public function itCorrectlyDetectsWhenTheResponseIsEmpty()
    {
        $this->assertFalse($this->normalResponse->isEmpty());
        $this->assertTrue($this->emptyDataResponse->isEmpty());
    }

    /** @test */
    public function itHandlesEmptyResponseInGetResults()
    {
        $this->assertEquals($this->emptyDataResponse->getResults(), null);
    }

    /** @test */
    public function itRetrievesFieldsCorrectlyFromNormalResponse()
    {
        $expected = [
            'id' => 1300005,
            'name_first' => '5th',
            'name_last' => 'Test'
        ];

        $this->assertTrue($this->normalResponse->getResults() instanceof \stdClass);
        $this->assertEquals((array)$this->normalResponse->getResults(), $expected);
    }

    /** @test */
    public function itExtractsTheErrorsFromBadResponse()
    {
        $firstErrorIndex = 0;
        $expected = ['message' => "There was an error"];

        $this->assertEquals((array)$this->erroredResponse->getErrors()[$firstErrorIndex], $expected);
    }
}
