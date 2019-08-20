<?php

namespace Tests\Unit\Position;

use App\Booking;
use App\Position;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $position;

    protected function setUp(): void
    {
        parent::setUp();

        $this->booking = factory(Booking::class)->make();
    }

    /** @test */
    public function itReturnsThePositionRelation()
    {
        $this->assertInstanceOf(Position::class, $this->booking->position);
    }
}
