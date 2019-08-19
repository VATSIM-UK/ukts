<?php

namespace Tests\Unit\Position;

use App\Position;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PositionUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $position;

    protected function setUp(): void
    {
        parent::setUp();

        $this->position = factory(Position::class)->make(['callsign' => 'EGGD_APP']);
    }

    /** @test */
    public function itReturnsTheCorrectSuffixAttribute()
    {
        $this->assertEquals('APP', $this->position->suffix);
    }

    /** @test */
    public function itReturnsTheCorrectSuffixWithSubPositions()
    {
        $subPosition = factory(Position::class)->make(['callsign' => 'EGGD_R_APP']);

        $this->assertEquals('APP', $subPosition->suffix);
    }
}
