<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Constants\ControllerRating;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ControllerConstantsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function itReturnsTheCorrectRatingByNumber()
    {
        $this->assertEquals('OBS', ControllerRating::getRatingCodeByNumber(0));
        $this->assertEquals('S1', ControllerRating::getRatingCodeByNumber(1));
        $this->assertEquals('S2', ControllerRating::getRatingCodeByNumber(2));
        $this->assertEquals('S3', ControllerRating::getRatingCodeByNumber(3));
        $this->assertEquals('C1', ControllerRating::getRatingCodeByNumber(4));
    }

    /** @test */
    public function it_returns_the_human_readable_format_based_on_code()
    {
        $this->assertEquals('Observer', ControllerRating::getHumanReadableRatingByCode('OBS'));
        $this->assertEquals('Student 1', ControllerRating::getHumanReadableRatingByCode('S1'));
        $this->assertEquals('Student 2', ControllerRating::getHumanReadableRatingByCode('S2'));
        $this->assertEquals('Senior Student', ControllerRating::getHumanReadableRatingByCode('S3'));
        $this->assertEquals('Controller 1', ControllerRating::getHumanReadableRatingByCode('C1'));
        $this->assertEquals('Controller 3', ControllerRating::getHumanReadableRatingByCode('C3'));
    }

    /** @test */
    public function it_returns_true_when_minimum_rating_is_the_same_as_given()
    {
        // TWR suffix requires a rating of 2 or higher
        $this->assertTrue(ControllerRating::isValidRatingForSuffix('TWR', 2));
    }

    /** @test */
    public function it_returns_true_when_minimum_rating_higher_than_required()
    {
        // TWR suffix requires a rating of two or higher.
        $this->assertTrue(ControllerRating::isValidRatingForSuffix('TWR', 3));
    }

    /** @test */
    public function it_returns_false_when_minimum_rating_is_higher()
    {
        // CTR suffix requires a rating of 4 or higher
        $this->assertFalse(ControllerRating::isValidRatingForSuffix('CTR', 1));
    }

    /** @test */
    public function it_fails_gracefully_when_invalid_suffix_is_provided()
    {
        $this->assertNull(ControllerRating::isValidRatingForSuffix('BLEH', 2));
    }

    /** @test */
    public function it_handles_lowercase_suffix_correctly()
    {
        $this->assertFalse(ControllerRating::isValidRatingForSuffix('ctr', 1));
        $this->assertTrue(ControllerRating::isValidRatingForSuffix('ctr', 4));
    }
}
