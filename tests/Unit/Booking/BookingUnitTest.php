<?php

namespace Tests\Unit\Booking;

use App\Modules\Bookings\Booking;
use App\Modules\Position\Position;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\Booking\BookingsTestHelper;

class BookingUnitTest extends TestCase
{
    use RefreshDatabase, BookingsTestHelper;

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

    /** @test */
    public function itReturnsTheUserRelation()
    {
        $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('find')
                ->andReturn($this->mockedUser());
        })->makePartial();

        $this->assertInstanceOf(User::class, $this->booking->user);
    }
}
