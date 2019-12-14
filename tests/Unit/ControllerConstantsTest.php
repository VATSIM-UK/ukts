<?php

namespace Tests\Unit;

use App\Constants\ControllerRating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ControllerConstantsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function itReturnsTheCorrectRatingByNumber()
    {
        $this->assertEquals('OBS', ControllerRating::getRatingCodeByNumber(1));
        $this->assertEquals('S1', ControllerRating::getRatingCodeByNumber(2));
        $this->assertEquals('S2', ControllerRating::getRatingCodeByNumber(3));
        $this->assertEquals('S3', ControllerRating::getRatingCodeByNumber(4));
        $this->assertEquals('C1', ControllerRating::getRatingCodeByNumber(5));
    }

    /** @test */
    public function itReturnsHumanReadableFormatBasedOnCode()
    {
        $this->assertEquals('Observer', ControllerRating::getHumanReadableRatingByCode('OBS'));
        $this->assertEquals('Student 1', ControllerRating::getHumanReadableRatingByCode('S1'));
        $this->assertEquals('Student 2', ControllerRating::getHumanReadableRatingByCode('S2'));
        $this->assertEquals('Senior Student', ControllerRating::getHumanReadableRatingByCode('S3'));
        $this->assertEquals('Controller 1', ControllerRating::getHumanReadableRatingByCode('C1'));
        $this->assertEquals('Controller 3', ControllerRating::getHumanReadableRatingByCode('C3'));
    }

    /** @test */
    public function itReturnsTrueWhenMinimumRatingIsTheSameAsGiven()
    {
        // TWR suffix requires a rating of 2 or higher
        $this->assertTrue(ControllerRating::isValidRatingForSuffix('TWR', 3));
    }

    /** @test */
    public function itReturnsTrueWhenMinimumRatingHigherThanRequired()
    {
        // TWR suffix requires a rating of two or higher.
        $this->assertTrue(ControllerRating::isValidRatingForSuffix('TWR', 4));
    }

    /** @test */
    public function itReturnsFalseWhenMinimumRatingIsHigher()
    {
        // CTR suffix requires a rating of 4 or higher
        $this->assertFalse(ControllerRating::isValidRatingForSuffix('CTR', 1));
    }

    /** @test */
    public function itFailsGracefullyWhenInvalidSuffixIsProvided()
    {
        $this->assertFalse(ControllerRating::isValidRatingForSuffix('BLEH', 2));
    }

    /** @test */
    public function itHandlesLowercaseSuffixCorrectly()
    {
        $this->assertFalse(ControllerRating::isValidRatingForSuffix('ctr', 1));
        $this->assertTrue(ControllerRating::isValidRatingForSuffix('ctr', 5));
    }
}
