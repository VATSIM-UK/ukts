<?php

namespace Tests\Unit\SessionRequest;

use App\Modules\Bookings\Booking;
use App\Modules\SessionRequest\SessionRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class SessionRequestUnitTest extends TestCase
{
    use RefreshDatabase, UserHelper;

    /** @test */
    public function itCanBeRelatedToABooking()
    {
        $booking = factory(Booking::class)->create();
        $sessionRequest = factory(SessionRequest::class)->create(['booking_id' => $booking->id]);

        $this->assertInstanceOf(Booking::class, $sessionRequest->booking);
    }

    /** @test */
    public function itDetectsWhenHasBeenTakenByAUser()
    {
        $sessionRequest = factory(SessionRequest::class)->create([
            'taken_on' => Carbon::now(), 'taken_by' => $this->mockUserId
        ]);

        $this->assertTrue($sessionRequest->isTaken());
    }

    /** @test */
    public function itDetectsWhenHasNotBeenTakenByAUser()
    {
        $sessionRequest = factory(SessionRequest::class)->create(['taken_on' => null, 'taken_by' => null]);

        $this->assertFalse($sessionRequest->isTaken());
    }
}
