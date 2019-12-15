<?php

namespace Tests\Feature\Booking;

use App\Modules\Bookings\Booking;
use App\Modules\Bookings\BookingsService;
use App\Position;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests;

    /* @test */
    public function testItReturnsBookingsPaginated()
    {
        $bookings = factory(Booking::class, 15)->create();

        $data = $this->graphQL('
        {
            bookings (first: 10) {
                data {
                    id
                    position {
                      id
                      callsign
                    }
                }
                paginatorInfo {
                    currentPage
                    lastPage
                }
            }
        }
        ')->assertJsonStructure([
            'data' => [
                'bookings' => [
                    'data' => [
                        [
                            'id',
                            'position' => [
                                'id',
                                'callsign',
                            ],
                        ],
                    ],
                    'paginatorInfo' => [
                        'currentPage',
                        'lastPage',
                    ],
                ],
            ],
        ]);
    }

    /* @test */
    public function testItReturnsBookingsForDate()
    {
        factory(Booking::class)->create([
            'from' => '2019-08-10 14:00:00',
            'to' => '2019-08-10 15:00:00',
        ]);
        factory(Booking::class)->create([
            'from' => '2019-08-09 14:00:00',
            'to' => '2019-08-09 15:00:00',
        ]);
        factory(Booking::class)->create([
            'from' => '2019-08-09 20:00:00',
            'to' => '2019-08-09 22:30:00',
        ]);

        $data = $this->graphQL('
        {
            bookingsByDate (from: "2019-08-09") {
                id
            }
        }
        ')->assertJson([
            'data' => [
                'bookingsByDate' => [
                    ['id' => 2],
                    ['id' => 3],
                ],
            ],
        ]);
    }

    /* @test */
    public function testItReturnsBookingsBetweenDates()
    {
        factory(Booking::class)->create([
            'from' => '2019-08-06 14:00:00',
            'to' => '2019-08-06 15:00:00',
        ]);
        factory(Booking::class)->create([
            'from' => '2019-08-09 20:00:00',
            'to' => '2019-08-09 22:30:00',
        ]);
        factory(Booking::class)->create([
            'from' => '2019-08-04 14:00:00',
            'to' => '2019-08-04 15:00:00',
        ]);
        factory(Booking::class)->create([
            'from' => '2019-08-10 14:00:00',
            'to' => '2019-08-10 15:00:00',
        ]);

        $data = $this->graphQL('
        {
            bookingsBetweenDates (from: {
                from: "2019-08-05"
                to: "2019-08-09"
            }) {
                id
            }
        }
        ')->assertJson([
            'data' => [
                'bookingsBetweenDates' => [
                    ['id' => 1],
                    ['id' => 2],
                ],
            ],
        ]);
    }

    /* @test */
    public function testItReturnsBookingById()
    {
        $bookings = factory(Booking::class, 3)->create();

        $this->graphQL("
        {
            booking(id: {$bookings->get(1)->id}) {
                id
                position {
                    callsign
                }
            }
        }
        ")->assertJson([
            'data' => [
                'booking' => [
                    'id' => $bookings->get(1)->id,
                    'position' => [
                        'callsign' => $bookings->get(1)->position->callsign,
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function testValidBookingCanBeCreated()
    {
        factory(Position::class)->create(['callsign' => 'EGGD_GND']);

        $this->mock(BookingsService::class, function ($mock) {
            $mock->shouldReceive('validateRatingRequirement')->andReturn(true);
            $mock->shouldReceive('validateBookingTimes')->andReturn(true);
            $mock->shouldReceive('createBooking')->passthru();
        })->makePartial();

        $data = $this->graphQL('
          mutation {
            createBooking(user_id: 1300001, position_id: 1, from:"2019-08-20 15:00:00", to:"2019-08-20 16:30:00") {
                id
            }
          }');

        $this->assertDatabaseHas('bookings', [
            'id' => $data->json('data.createBooking.id'),
            'user_id' => 1300001,
            'position_id' => 1,
            'from' => new Carbon('2019-08-20 15:00:00'),
            'to' => new Carbon('2019-08-20 16:30:00'),
        ]);

        $data = $this->graphQL('
          mutation {
            createBooking(user_id: 1300001, position_id: 1, from:"2019-08-20 16:30:00", to:"2019-08-20 17:30:00") {
                id
            }
          }');

        $this->assertDatabaseHas('bookings', [
            'id' => $data->json('data.createBooking.id'),
            'user_id' => 1300001,
            'position_id' => 1,
            'from' => new Carbon('2019-08-20 16:30:00'),
            'to' => new Carbon('2019-08-20 17:30:00'),
        ]);
    }

    /** @test */
    public function testInvalidBookingCantBeCreated()
    {
        $pos = factory(Position::class)->create();

        // Invalid "from" date
        $this->graphQL('
          mutation {
            createBooking(user_id: 1300001, position: {
                connect: 1
            }, from:"Blah", to:"2019-08-20 16:30:00") {
                id
            }
          }')->assertJsonStructure([
                'errors' => [
                    [
                        'message',
                        'extensions',
                        'locations',
                    ],
                ],
            ]
        );

        // Invalid Position
        $this->graphQL('
          mutation {
            createBooking(user_id: 1300001, position: {
                connect: 2
            }, from:"2019-08-20 15:00:00", to:"2019-08-20 16:30:00") {
                id
            }
          }')->assertJsonStructure([
                'errors' => [
                    [
                        'message',
                        'extensions',
                        'locations',
                    ],
                ],
            ]
        );

        // "From" and "To" the same
        $this->graphQL('
          mutation {
            createBooking(user_id: 1300001, position: {
                connect: 1
            }, from:"2019-08-20 15:00:00", to:"2019-08-20 15:00:00") {
                id
            }
          }')->assertJsonStructure([
                'errors' => [
                    [
                        'message',
                        'extensions',
                        'locations',
                    ],
                ],
            ]
        );

        // "From" after "To"
        $this->graphQL('
          mutation {
            createBooking(user_id: 1300001, position: {
                connect: 1
            }, from:"2019-08-20 15:30:00", to:"2019-08-20 15:00:00") {
                id
            }
          }')->assertJsonStructure([
                'errors' => [
                    [
                        'message',
                        'extensions',
                        'locations',
                    ],
                ],
            ]
        );
    }

    private function bypassRatingCheckViaMock()
    {
        $this->mock(BookingsService::class, function ($mock) {
            $mock->shouldReceive('validateRatingRequirement')->andReturn(true);
        })->makePartial();
    }

    /** @test */
    public function testOverlappingBookingsCannotBeCreatedIntersectingOnStart()
    {
        $this->bypassRatingCheckViaMock();

        $position = factory(Position::class)->create()->id;

        // Create two bookings for position 1
        factory(Booking::class)->create([
            'position_id' => $position,
            'from' => '2019-08-10 14:00:00',
            'to' => '2019-08-10 15:00:00',
        ]);

        // Test 1: Doesn't allow to book inside of booking
        $this->graphQL("
          mutation {
            createBooking(user_id: 1300001, position_id: {$position}, from:\"2019-08-10 14:30:00\", to:\"2019-08-10 15:30:00\") {
                id
            }
          }")->assertJsonPath('errors.0.message', "Can't have overlapping bookings for the same position!");
    }

    /** @test */
    public function testOverlappingBookingsCannotBeCreatedOverlappingOnEnd()
    {
        $this->bypassRatingCheckViaMock();

        $position = factory(Position::class)->create()->id;

        factory(Booking::class)->create([
            'position_id' => $position,
            'from' => '2019-08-10 19:00:00',
            'to' => '2019-08-10 20:00:00',
        ]);

        $this->graphQL("
          mutation {
            createBooking(user_id: 1300001, position_id: {$position}, from:\"2019-08-10 18:30:00\", to:\"2019-08-10 19:30:00\") {
                id
            }
          }")->assertJsonPath('errors.0.message', "Can't have overlapping bookings for the same position!");
    }

    /** @test */
    public function testOverlappingBookings()
    {
        $this->mock(BookingsService::class, function ($mock) {
            $mock->shouldReceive('validateRatingRequirement')->andReturn(true);
        })->makePartial();

        $position = factory(Position::class)->create()->id;
        $position2 = factory(Position::class)->create()->id;

        // Create two bookings for position 1
        factory(Booking::class)->create([
            'position_id' => $position,
            'from' => '2019-08-10 14:00:00',
            'to' => '2019-08-10 15:00:00',
        ]);

        $booking2 = factory(Booking::class)->create([
            'position_id' => $position,
            'from' => '2019-08-10 19:00:00',
            'to' => '2019-08-10 20:00:00',
        ]);


        $this->graphQL("
          mutation {
            updateBooking(id: {$booking2->id}, from:\"2019-08-10 14:30:00\", to:\"2019-08-10 15:30:00\") {
                id
            }
          }")->assertJsonPath('errors.0.message', "Can't have overlapping bookings for the same position!");

        // Test 2: Doesn't allow to book over booking
        $this->graphQL("
          mutation {
            createBooking(user_id: 1300001, position: {
                connect: {$position}
            }, from:\"2019-08-10 14:00:00\", to:\"2019-08-10 15:00:00\") {
                id
            }
          }")->assertJsonPath('errors.0.message', "Can't have overlapping bookings for the same position!");

        $this->graphQL("
          mutation {
            updateBooking(id: {$booking2->id}, from:\"2019-08-10 14:00:00\", to:\"2019-08-10 15:00:00\") {
                id
            }
          }")->assertJsonPath('errors.0.message', "Can't have overlapping bookings for the same position!");

        // Test 3: Allows if not on same position as other booking
        $this->graphQL("
          mutation {
            createBooking(user_id: 1300001, position_id: {$position2}
            }, from:\"2019-08-10 14:30:00\", to:\"2019-08-10 15:30:00\") {
                id
            }
          }")->assertJsonPath('data.createBooking.id', 3);
        $this->graphQL("
          mutation {
            updateBooking(id: {$booking2->id}, position: {
                connect: {$position2}
            } from:\"2019-08-10 15:30:00\", to:\"2019-08-10 16:30:00\") {
                id
            }
          }")->assertJsonPath('data.updateBooking.id', $booking2->id);

        // Test 4: Can create booking in empty space
        $this->graphQL("
          mutation {
            createBooking(user_id: 1300001, position_id: {$position}, from:\"2019-08-10 18:30:00\", to:\"2019-08-10 19:00:00\") {
                id
            }
          }")->assertJsonPath('data.createBooking.id', 4);
    }

    /** @test */
    public function testItCanUpdateABooking()
    {
        factory(Booking::class)->create([
            'id' => 1,
            'user_id' => 1300001,
            'position_id' => 1,
            'from' => new Carbon('2019-08-20 15:00:00'),
            'to' => new Carbon('2019-08-20 16:30:00'),
        ]);

        $this->graphQL('
            mutation {
                updateBooking(id: 1, to: "2019-08-20 17:00:00") {
                    id
                }
            }')->assertJson([
            'data' => [
                'updateBooking' => [
                    'id' => 1,
                ],
            ],
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => 1,
            'to' => new Carbon('2019-08-20 17:00:00'),
        ]);
    }

    /** @test */
    public function testItCanDeleteABooking()
    {
        factory(Booking::class)->create();

        $this->assertEquals(1, Booking::count());

        $this->graphQL('
            mutation {
                deleteBooking(id: 1) {
                    id
                }
            }')->assertJson([
            'data' => [
                'deleteBooking' => [
                    'id' => 1,
                ],
            ],
        ]);

        $this->assertEquals(0, Booking::count());
    }
}
