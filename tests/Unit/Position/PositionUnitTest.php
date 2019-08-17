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

        $this->position = factory(Position::class)->create(['callsign' => 'EGGD_APP']);
    }

    /** @test */
    public function it_returns_the_correct_suffix_attribute()
    {
        $this->assertEquals('APP', $this->position->suffix);
    }
}
