<?php

namespace Tests\Unit\Booking;

use App\Modules\Bookings\Booking;
use App\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $position;
    protected $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->booking = factory(Booking::class)->create(['user_id' => 1234567]);
    }

    /** @test */
    public function itReturnsThePositionRelation()
    {
        $this->assertInstanceOf(Position::class, $this->booking->position);
    }
}
