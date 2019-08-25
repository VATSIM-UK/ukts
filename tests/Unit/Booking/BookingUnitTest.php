<?php

namespace Tests\Unit\Position;

use App\Booking;
use App\Exceptions\OverlappingBookingException;
use App\Position;
use Carbon\Carbon;
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

    /** @test */
    public function itCorrectlyDeterminesIfABookingCanBeMade()
    {
        $this->booking->from = new Carbon('10th January 2018 15:00:00');
        $this->booking->to = new Carbon('10th January 2018 16:00:00');
        $this->booking->save();

        $this->assertTrue(Booking::canBeMade(
            $this->booking->position_id,
            new Carbon('10th January 2018 14:00:00'),
            new Carbon('10th January 2018 15:00:00'))
        );
        $this->assertFalse(Booking::canBeMade(
            $this->booking->position_id,
            new Carbon('10th January 2018 14:00:00'),
            new Carbon('10th January 2018 15:30:00'))
        );
        $this->assertFalse(Booking::canBeMade(
            $this->booking->position_id,
            new Carbon('10th January 2018 15:59:00'),
            new Carbon('10th January 2018 18:00:00'))
        );
    }

    /** @test */
    public function itWillThrowExceptionIfBookingCantBeMade()
    {
        $this->expectException(OverlappingBookingException::class);
        $this->booking->from = new Carbon('10th January 2018 15:00:00');
        $this->booking->to = new Carbon('10th January 2018 16:00:00');
        $this->booking->save();

        Booking::create([
            'user_id' => $this->booking->user_id,
            'position_id' => $this->booking->position_id,
            'from' => new Carbon('10th January 2018 14:00:00'),
            'to' => new Carbon('10th January 2018 15:30:00')
        ]);
    }

    /** @test */
    public function itWontThrowExceptionIfBookingCanBeMade()
    {
        $this->expectNotToPerformAssertions();
        $this->booking->from = new Carbon('10th January 2018 15:00:00');
        $this->booking->to = new Carbon('10th January 2018 16:00:00');
        $this->booking->save();

        Booking::create([
            'user_id' => $this->booking->user_id,
            'position_id' => $this->booking->position_id,
            'from' => new Carbon('10th January 2018 14:00:00'),
            'to' => new Carbon('10th January 2018 15:00:00')
        ]);
    }
}
