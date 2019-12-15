<?php

namespace Tests\Feature\Booking;

use App\Modules\Bookings\Booking;
use App\Position;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;
use Tests\Unit\Booking\BookingsTestHelper;

class BookingTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests, BookingsTestHelper;

    protected $position;

    protected function setUp(): void
    {
        parent::setUp();

        $this->position = factory(Position::class)->create();
    }

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
        $creationJsonFormat = ['data' => ['createBooking' => ['id']]];
        $this->position->callsign = 'EGGD_GND';
        $this->position->save();

        $this->mockUserFind();

        $data = $this->graphQL("
          mutation {
            createBooking(user_id: {$this->mockUserId}, position_id: {$this->position->id}, from:\"2019-08-20 15:00:00\", to:\"2019-08-20 16:30:00\") {
                id
            }
          }")->assertJsonStructure($creationJsonFormat);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->mockUserId,
            'position_id' => $this->position->id,
            'from' => new Carbon('2019-08-20 15:00:00'),
            'to' => new Carbon('2019-08-20 16:30:00'),
        ]);

        $data = $this->graphQL("
          mutation {
            createBooking(user_id: {$this->mockUserId}, position_id: {$this->position->id}, from: \"2019-08-20 16:30:00\", to:\"2019-08-20 17:30:00\") {
                id
            }
          }")->assertJsonStructure($creationJsonFormat);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->mockUserId,
            'position_id' => $this->position->id,
            'from' => new Carbon('2019-08-20 16:30:00'),
            'to' => new Carbon('2019-08-20 17:30:00'),
        ]);
    }

    /** @test */
    public function testInvalidBookingCantBeCreated()
    {
        $this->mockUserFind();

        // Invalid "from" date
        $this->graphQL("
          mutation {
            createBooking(user_id: {$this->mockUserId}, position_id: {$this->position->id}, from:'Blah', to:'2019-08-20 16:30:00') {
                id
            }
          }")->assertJsonStructure([
                'errors' => [
                    [
                        'message',
                        'extensions',
                        'locations',
                    ],
                ],
            ]
        );

        $unknownPositionId = 2;
        // Invalid Position
        $this->graphQL("
          mutation {
            createBooking(user_id: {$this->mockUserId}, position_id: {$unknownPositionId}, from: '2019-08-20 15:00:00', to:'2019-08-20 16:30:00') {
                id
            }
          }")->assertJsonStructure([
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
        $this->graphQL("
          mutation {
            createBooking(user_id: {$this->mockUserId}, position_id: 1
            }, from:\"2019-08-20 15:00:00\", to:\"2019-08-20 15:00:00\") {
                id
            }
          }")->assertJsonStructure([
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
        $this->graphQL("
          mutation {
            createBooking(user_id: 1300001, position_id: {$this->position->id}, from:\"2019-08-20 15:30:00\", to:\"2019-08-20 15:00:00\") {
                id
            }
          }")->assertJsonStructure([
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

    /** @test */
    public function testOverlappingBookingsCannotBeCreatedOverlappingOnStart()
    {
        $this->setupOverlappingTest();

        // Create two bookings for position 1
        factory(Booking::class)->create([
            'position_id' => $this->position->id,
            'from' => '2019-08-10 14:00:00',
            'to' => '2019-08-10 15:00:00',
        ]);

        // Test 1: Doesn't allow to book inside of booking
        $this->graphQL("
          mutation {
            createBooking(user_id: {$this->mockUserId}, position_id: {$this->position->id}, from:\"2019-08-10 14:30:00\", to:\"2019-08-10 15:30:00\") {
                id
            }
          }")->assertJsonPath('errors.0.message', "Can't have overlapping bookings for the same position!");
    }

    /** @test */
    public function testOverlappingBookingsCannotBeCreatedOverlappingOnEnd()
    {
        $this->setupOverlappingTest();

        factory(Booking::class)->create([
            'position_id' => $this->position->id,
            'from' => '2019-08-10 19:00:00',
            'to' => '2019-08-10 20:00:00',
        ]);

        $this->graphQL("
          mutation {
            createBooking(user_id: {$this->mockUserId}, position_id: {$this->position->id}, from:\"2019-08-10 18:30:00\", to:\"2019-08-10 19:30:00\") {
                id
            }
          }")->assertJsonPath('errors.0.message', "Can't have overlapping bookings for the same position!");
    }

    /** @test */
    public function testAllowsOverlappingBookingsOnDifferentPositions()
    {
        $this->setupOverlappingTest();

        $creationJsonFormat = ['data' => ['createBooking' => ['id']]];
        // twr position to meet the default rating check in setupOverlappingTest.
        $differentPosition = factory(Position::class)->create(['callsign' => 'EGFF_TWR']);

        $this->graphQL("
          mutation {
            createBooking(user_id: {$this->mockUserId}, position_id: {$this->position->id}, from:\"2019-08-10 18:30:00\", to:\"2019-08-10 19:30:00\") {
                id
            }
          }")->assertJsonStructure($creationJsonFormat);

        $this->graphQL("
          mutation {
            createBooking(user_id: {$this->mockUserId}, position_id: {$differentPosition->id}, from:\"2019-08-10 18:30:00\", to:\"2019-08-10 19:30:00\") {
                id
            }
          }")->assertJsonStructure($creationJsonFormat);
    }

    /** @test */
    public function testAllowsBookingInEmptySpaceForSamePosition()
    {
        $this->setupOverlappingTest();

        $firstBooking = factory(Booking::class)->create([
            'position_id' => $this->position->id,
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 14:00:00'),
        ]);

        $secondBooking = factory(Booking::class)->create([
            'position_id' => $this->position->id,
            'from' => new Carbon('10th January 2019 15:00:00'),
            'to' => new Carbon('10th January 2019 16:00:00'),
        ]);

        $this->graphQL("
          mutation {
            createBooking(user_id: {$this->mockUserId}, position_id: {$this->position->id}, from:\"2019-01-10 14:00:00\", to:\"2019-01-10 15:00:00\") {
                id
            }
          }")->assertJsonStructure(['data' => ['createBooking' => ['id']]]);
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

    /**
     * Setup a test case to pass the rating check for testing overlapping.
     * @param  string  $callsign
     */
    private function setupOverlappingTest($callsign = 'EGGD_TWR')
    {
        // ensure position in valid inline with the mockUserFind method.
        $this->position->callsign = $callsign;
        $this->position->save();
        $this->mockUserFind();
    }
}
