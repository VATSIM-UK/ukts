<?php

namespace Tests\Unit\Booking;

use App\Modules\Bookings\Booking;
use App\Modules\Position\Position;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class BookingUnitTest extends TestCase
{
    use RefreshDatabase, UserHelper;

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
        $this->assertInstanceOf(User::class, $this->booking->user);
    }
}
