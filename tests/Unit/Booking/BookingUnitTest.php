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
//
//    /** @test */
//    public function itWillThrowExceptionIfBookingCantBeMade()
//    {
//        $this->expectException(OverlappingBookingException::class);
//        $this->booking->from = new Carbon('10th January 2018 15:00:00');
//        $this->booking->to = new Carbon('10th January 2018 16:00:00');
//        $this->booking->save();
//
//        Booking::create([
//            'user_id' => $this->booking->user_id,
//            'position_id' => $this->booking->position_id,
//            'from' => new Carbon('10th January 2018 14:00:00'),
//            'to' => new Carbon('10th January 2018 15:30:00'),
//        ]);
//    }
//
//    /** @test */
//    public function itWontThrowExceptionIfBookingCanBeMade()
//    {
//        $this->mock(User::class, function ($mock) {
//            $mock->shouldReceive('get')->andReturn(
//                User::initModelWithData([
//                    'id' => 1234567,
//                    'name_fist' => 'First',
//                    'name_last' => 'Last',
//                    'atcRating' => [
//                        'code' => 'S3',
//                        'vatsim_id' => 4
//                    ]
//                ])
//            );
//        })->makePartial();
//
//        $this->expectNotToPerformAssertions();
//        $this->booking->from = new Carbon('10th January 2018 15:00:00');
//        $this->booking->to = new Carbon('10th January 2018 16:00:00');
//        $this->booking->save();
//
//        Booking::create([
//            'user_id' => $this->booking->user_id,
//            'position_id' => $this->booking->position_id,
//            'from' => new Carbon('10th January 2018 14:00:00'),
//            'to' => new Carbon('10th January 2018 15:00:00'),
//        ]);
//    }
}
