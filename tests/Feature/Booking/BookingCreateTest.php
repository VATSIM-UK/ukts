<?php

namespace Tests\Feature\Booking;

use App\Modules\Bookings\Booking;
use App\Modules\Endorsement\Solo\SoloEndorsement;
use App\Modules\Endorsement\Special\Assignment;
use App\Modules\Endorsement\Special\SpecialEndorsement;
use App\Modules\Position\Position;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class BookingCreateTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests, UserHelper;

    protected $position;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->mockedUser());
        $this->mockUserFind();

        $this->position = factory(Position::class)->create();
    }

    /** @test */
    public function testValidBookingCanBeCreated()
    {
        $this->withoutExceptionHandling();
        $creationJsonFormat = ['data' => ['createBooking' => ['id']]];
        $this->position->callsign = 'EGGD_GND';
        $this->position->save();

        $this->graphQL("
          mutation {
            createBooking(
                input: {
                    position_id: {$this->position->id},
                    network_type: 0,
                    from:\"2019-08-20 15:00:00\",
                    to:\"2019-08-20 16:30:00\"
                }
            )
            {
                id
            }
          }")->assertJsonStructure($creationJsonFormat);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->mockUserId,
            'position_id' => $this->position->id,
            'from' => new Carbon('2019-08-20 15:00:00'),
            'to' => new Carbon('2019-08-20 16:30:00'),
        ]);

        $this->graphQL("
          mutation {
            createBooking(
                input: {
                    position_id: {$this->position->id},
                    network_type: 0,
                    from: \"2019-08-20 16:30:00\",
                    to:\"2019-08-20 17:30:00\"
                }
            )
            {
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
        // Invalid "from" date
        $this->graphQL("
          mutation {
            createBooking(
                input: {
                    position_id: {$this->position->id},
                    network_type: 0,
                    from:'Blah',
                    to:'2019-08-20 16:30:00'
                }
            )
            {
                id
            }
          }")->assertJsonStructure(
            [
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
            createBooking(
                input: {
                    position_id: {$unknownPositionId},
                    network_type: 0,
                    from: '2019-08-20 15:00:00',
                    to:'2019-08-20 16:30:00'
                }
            )
            {
                id
            }
          }")->assertJsonStructure(
            [
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
            createBooking(
                input: {
                    position_id: 1,
                    network_type: 0,
                    from:"2019-08-20 15:00:00",
                    to:"2019-08-20 15:00:00"
                }
            )
            {
                id
            }
          }')->assertJsonStructure(
            [
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
            createBooking(user_id: 1300001, position_id: {$this->position->id}, network_type: 0, from:\"2019-08-20 15:30:00\", to:\"2019-08-20 15:00:00\") {
                id
            }
          }")->assertJsonStructure(
            [
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
    public function testOverlappingBookingsCannotBeCreatedOverlappingOnEnd()
    {
        $this->bypassRatingChecks();

        // Create two bookings for position 1
        factory(Booking::class)->create([
            'position_id' => $this->position->id,
            'from' => '2019-08-10 14:00:00',
            'to' => '2019-08-10 15:00:00',
        ]);

        // Test 1: Doesn't allow to book inside of booking
        $this->graphQL("
          mutation {
            createBooking(
                input: {
                    position_id: {$this->position->id},
                    network_type: 0,
                    from:\"2019-08-10 14:30:00\",
                    to:\"2019-08-10 15:30:00\"
                }
            )
            {
                id
            }
          }")->assertJsonPath('errors.0.message', "Can't have overlapping bookings for the same position!");
    }

    /** @test */
    public function testOverlappingBookingsCannotBeCreatedOverlappingOnStart()
    {
        $this->bypassRatingChecks();

        factory(Booking::class)->create([
            'position_id' => $this->position->id,
            'from' => '2019-08-10 19:00:00',
            'to' => '2019-08-10 20:00:00',
        ]);

        $this->graphQL("
          mutation {
            createBooking(
                input: {
                    position_id: {$this->position->id},
                    network_type: 0,
                    from:\"2019-08-10 18:30:00\",
                    to:\"2019-08-10 19:30:00\"
                }
            )
            {
                id
            }
          }")->assertJsonPath('errors.0.message', "Can't have overlapping bookings for the same position!");
    }

    /** @test */
    public function testAllowsOverlappingBookingsOnDifferentPositions()
    {
        $this->bypassRatingChecks();

        $creationJsonFormat = ['data' => ['createBooking' => ['id']]];
        // twr position to meet the default rating check in setupOverlappingTest.
        $differentPosition = factory(Position::class)->create(['callsign' => 'EGFF_TWR']);

        $this->graphQL("
          mutation {
            createBooking(
                input: {
                    position_id: {$this->position->id},
                    network_type: 0,
                    from:\"2019-08-10 18:30:00\",
                    to:\"2019-08-10 19:30:00\"
                }
            )
            {
                id
            }
          }")->assertJsonStructure($creationJsonFormat);

        $this->graphQL("
          mutation {
            createBooking(
                input: {
                    position_id: {$differentPosition->id},
                    network_type: 0,
                    from:\"2019-08-10 18:30:00\",
                    to:\"2019-08-10 19:30:00\"
                }
            )
            {
                id
            }
          }")->assertJsonStructure($creationJsonFormat);
    }

    /** @test */
    public function testAllowsBookingInEmptySpaceForSamePosition()
    {
        $this->bypassRatingChecks();

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
            createBooking(
                input: {
                    position_id: {$this->position->id},
                    network_type: 0,
                    from:\"2019-01-10 14:00:00\",
                    to:\"2019-01-10 15:00:00\"
                }
            )
            {
                id
            }
          }")->assertJsonStructure(['data' => ['createBooking' => ['id']]]);
    }

    /** @test */
    public function testAllowsBookingCreationWhenRequiredSpecialEndorsementIsFound()
    {
        $this->bypassRatingChecks();

        $specialEndorsement = factory(SpecialEndorsement::class)->create();

        // create a constraint for the position.
        DB::table('special_endorsement_positions')->insert([
            'endorsement_id' => $specialEndorsement->id,
            'position_id' => $this->position->id,
        ]);

        // assign the endorsement to the user.
        Assignment::create([
            'user_id' => $this->mockUserId,
            'endorsement_id' => $specialEndorsement->id,
            'granted_by' => $this->mockUserId,
        ]);

        $this->graphQL("
          mutation {
            createBooking(
                input: {
                    position_id: {$this->position->id},
                    network_type: 0,
                    from:\"2019-01-10 14:00:00\",
                    to:\"2019-01-10 15:00:00\"
                }
            )
            {
                id
            }
        }")->assertJsonStructure(['data' => ['createBooking' => ['id']]]);
    }

    /** @test */
    public function testDoesntAllowBookingRequiringSpecialEndorsementWhenUserNotEndorsed()
    {
        $this->bypassRatingChecks();

        $specialEndorsement = factory(SpecialEndorsement::class)->create();

        // create a constraint for the position
        DB::table('special_endorsement_positions')->insert([
            'endorsement_id' => $specialEndorsement->id,
            'position_id' => $this->position->id,
        ]);

        $this->graphQL("
          mutation {
            createBooking(
                input: {
                    position_id: {$this->position->id},
                    network_type: 0,
                    from:\"2019-01-10 14:00:00\",
                    to:\"2019-01-10 15:00:00\"
                }
            )
            {
                id
            }
        }")->assertJsonPath(
            'errors.0.message',
            'You do not have the required Special Endorsement to book this position.'
        );
    }

    /** @test */
    public function testAllowsOverlappingBookingsOfDifferentNetworkTypes()
    {
        $this->bypassRatingChecks();
        $this->position->callsign = 'EGFF_TWR';
        $this->position->save();

        $creationJsonFormat = ['data' => ['createBooking' => ['id']]];

        $this->graphQL("
          mutation {
            createBooking(
                input: {
                    position_id: {$this->position->id},
                    network_type: 0,
                    from:\"2019-08-10 18:30:00\",
                    to:\"2019-08-10 19:30:00\"
                }
            )
            {
                id
            }
          }")->assertJsonStructure($creationJsonFormat);

        $this->graphQL("
          mutation {
            createBooking(
                input: {
                    position_id: {$this->position->id},
                    network_type: 1,
                    from:\"2019-08-10 18:30:00\",
                    to:\"2019-08-10 19:30:00\"
                }
            )
            {
                id
            }
          }")->assertJsonStructure($creationJsonFormat);
    }

    /** @test */
    public function testDoesntAllowBookingOfInvalidNetworkType()
    {

        $this->withoutExceptionHandling();
        $this->bypassRatingChecks();
        $this->position->callsign = 'EGFF_TWR';
        $this->position->save();

        $this->graphQL("
          mutation {
            createBooking(
                input: {
                    position_id: {$this->position->id},
                    network_type: 2,
                    from:\"2019-08-10 18:30:00\",
                    to:\"2019-08-10 19:30:00\"
                }
            )
            {
                id
            }
          }")->assertJsonPath(
            'errors.0.message',
            'Invalid network type!'
        );
    }

    /** @test */
    public function testDoesntAllowBookingNetworkTypeOverlap()
    {
        $this->bypassRatingChecks();
        $this->position->callsign = 'EGFF_TWR';
        $this->position->save();

        factory(Booking::class)->create([
            'position_id' => $this->position->id,
            'from' => '2019-08-10 19:00:00',
            'to' => '2019-08-10 20:00:00',
        ]);

        $this->graphQL("
          mutation {
            createBooking(
                input: {
                    position_id: {$this->position->id},
                    network_type: 0,
                    from:\"2019-08-10 18:30:00\",
                    to:\"2019-08-10 19:30:00\"
                }
            )
            {
                id
            }
          }")->assertJsonPath('errors.0.message', "Can't have overlapping bookings for the same position!");
    }

    /** @test */
    public function testAllowsBookingWhenRatingRequirementNotMetButSoloEndorsementIsActive()
    {
        // mocked user has an S2 rating
        $this->mockedUser();

        // lets set the position to require an S3
        $this->position->callsign = 'EGGD_APP';
        $this->position->save();

        // create the endorsement on our position...
        factory(SoloEndorsement::class)->create([
            'position_id' => $this->position->id, 'user_id' => $this->mockUserId,
        ]);

        $this->graphQL("
          mutation {
            createBooking(
                input: {
                    position_id: {$this->position->id},
                    from:\"2019-01-10 14:00:00\",
                    to:\"2019-01-10 15:00:00\"
                }
            )
            {
                id
            }
        }")->assertJsonStructure(['data' => ['createBooking' => ['id']]]);
    }

    /** @test */
    public function testDoesntAllowBookingWhenSoloEndorsementOnPositionHasExpired()
    {
        // mocked user has an S2 rating
        $this->mockedUser();

        // lets set the position to require an S3
        $this->position->callsign = 'EGGD_APP';
        $this->position->save();

        // create the endorsement on our position which has expired ...
        factory(SoloEndorsement::class)->create([
            'position_id' => $this->position->id,
            'user_id' => $this->mockUserId,
            'expiry_date' => Carbon::now()->subDay(),
        ]);

        $this->graphQL("
          mutation {
            createBooking(
                input: {
                    position_id: {$this->position->id},
                    from:\"2019-01-10 14:00:00\",
                    to:\"2019-01-10 15:00:00\"
                }
            )
            {
                id
            }
        }")->assertJsonPath('errors.0.message', 'Your rating is not high enough to book that position.');
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
    private function bypassRatingChecks($callsign = 'EGGD_TWR')
    {
        // ensure position in valid inline with the mockUserFind method.
        $this->position->callsign = $callsign;
        $this->position->save();
    }
}
