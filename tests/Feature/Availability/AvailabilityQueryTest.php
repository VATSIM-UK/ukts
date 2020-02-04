<?php
//
//namespace Tests\Feature\Booking;
//
//use App\Modules\Bookings\Booking;
//use Illuminate\Foundation\Testing\RefreshDatabase;
//use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
//use Tests\TestCase;
//use Tests\Unit\Booking\AvailabilityTestHelper;
//
//class AvailabilityQueryTest extends TestCase
//{
//    use RefreshDatabase, MakesGraphQLRequests, AvailabilityTestHelper;
//
//    protected function setUp(): void
//    {
//        parent::setUp();
//
//        $this->actingAs($this->mockedUser());
//        $this->mockUserFind();
//    }
//
//    /* @test */
//    public function testItReturnsBookingsPaginated()
//    {
//        $bookings = factory(Booking::class, 15)->create();
//
//        $data = $this->graphQL('
//        {
//            bookings (first: 10) {
//                data {
//                    id
//                    position {
//                      id
//                      callsign
//                    }
//                }
//                paginatorInfo {
//                    currentPage
//                    lastPage
//                }
//            }
//        }
//        ')->assertJsonStructure([
//            'data' => [
//                'bookings' => [
//                    'data' => [
//                        [
//                            'id',
//                            'position' => [
//                                'id',
//                                'callsign',
//                            ],
//                        ],
//                    ],
//                    'paginatorInfo' => [
//                        'currentPage',
//                        'lastPage',
//                    ],
//                ],
//            ],
//        ]);
//    }
//
//    /* @test */
//    public function testItReturnsBookingsForDate()
//    {
//        factory(Booking::class)->create([
//            'from' => '2019-08-10 14:00:00',
//            'to' => '2019-08-10 15:00:00',
//        ]);
//        factory(Booking::class)->create([
//            'from' => '2019-08-09 14:00:00',
//            'to' => '2019-08-09 15:00:00',
//        ]);
//        factory(Booking::class)->create([
//            'from' => '2019-08-09 20:00:00',
//            'to' => '2019-08-09 22:30:00',
//        ]);
//
//        $data = $this->graphQL('
//        {
//            bookingsByDate (from: "2019-08-09") {
//                id
//            }
//        }
//        ')->assertJson([
//            'data' => [
//                'bookingsByDate' => [
//                    ['id' => 2],
//                    ['id' => 3],
//                ],
//            ],
//        ]);
//    }
//
//    /* @test */
//    public function testItReturnsBookingsBetweenDates()
//    {
//        factory(Booking::class)->create([
//            'from' => '2019-08-06 14:00:00',
//            'to' => '2019-08-06 15:00:00',
//        ]);
//        factory(Booking::class)->create([
//            'from' => '2019-08-09 20:00:00',
//            'to' => '2019-08-09 22:30:00',
//        ]);
//        factory(Booking::class)->create([
//            'from' => '2019-08-04 14:00:00',
//            'to' => '2019-08-04 15:00:00',
//        ]);
//        factory(Booking::class)->create([
//            'from' => '2019-08-10 14:00:00',
//            'to' => '2019-08-10 15:00:00',
//        ]);
//
//        $data = $this->graphQL('
//        {
//            bookingsBetweenDates (from: {
//                from: "2019-08-05"
//                to: "2019-08-09"
//            }) {
//                id
//            }
//        }
//        ')->assertJson([
//            'data' => [
//                'bookingsBetweenDates' => [
//                    ['id' => 1],
//                    ['id' => 2],
//                ],
//            ],
//        ]);
//    }
//
//    /* @test */
//    public function testItReturnsBookingById()
//    {
//        $bookings = factory(Booking::class, 3)->create();
//
//        $this->graphQL("
//        {
//            booking(id: {$bookings->get(1)->id}) {
//                id
//                position {
//                    callsign
//                }
//            }
//        }
//        ")->assertJson([
//            'data' => [
//                'booking' => [
//                    'id' => $bookings->get(1)->id,
//                    'position' => [
//                        'callsign' => $bookings->get(1)->position->callsign,
//                    ],
//                ],
//            ],
//        ]);
//    }
//}
