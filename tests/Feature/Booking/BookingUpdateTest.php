<?php

namespace Tests\Feature\Booking;

use App\Modules\Bookings\Booking;
use App\Modules\Position\Position;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class BookingUpdateTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests, UserHelper;

    private $position;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->mockedUser());
        $this->mockUserFind();

        $this->position = factory(Position::class)->create();
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
            'network_type' => Booking::NETWORK_TYPE_LIVE,
        ]);

        $this->graphQL('
            mutation {
                updateBooking(
                    input: {
                        id: 1,
                        from: "2019-08-20 17:00:00",
                        to: "2019-08-20 18:00:00",
                        network_type: LIVE
                    }
                ) {
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
            'from' => new Carbon('2019-08-20 17:00:00'),
            'to' => new Carbon('2019-08-20 18:00:00'),
            'network_type' => Booking::NETWORK_TYPE_LIVE,
        ]);
    }

    /** @test */
    public function testDoesntAllowUpdateofInvalidNetworkType()
    {
        $bookingToUpdate = factory(Booking::class)->create([
            'position_id' => $this->position->id,
            'from' => new Carbon('2019-08-20 17:00:00'),
            'to' => new Carbon('2019-08-20 18:30:00'),
            'network_type' => Booking::NETWORK_TYPE_LIVE,
        ]);

        // start
        $this->graphQL("
            mutation {
                updateBooking(
                    input: {
                        id: {$bookingToUpdate->id},
                        from: \"2019-08-20 16:15:00\",
                        to: \"2019-08-20 17:00:00\",
                        network_type: DJAEHfk
                    }
                ) {
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
    public function testBookingNotUpdatedWhenOverlapsWithRelevantMessage()
    {
        factory(Booking::class)->create([
            'position_id' => $this->position->id,
            'from' => new Carbon('2019-08-20 15:00:00'),
            'to' => new Carbon('2019-08-20 16:30:00'),
            'network_type' => Booking::NETWORK_TYPE_LIVE,
        ]);

        $bookingToUpdate = factory(Booking::class)->create([
            'position_id' => $this->position->id,
            'from' => new Carbon('2019-08-20 17:00:00'),
            'to' => new Carbon('2019-08-20 18:30:00'),
            'network_type' => Booking::NETWORK_TYPE_LIVE,
        ]);

        // start
        $this->graphQL("
            mutation {
                updateBooking(
                    input: {
                        id: {$bookingToUpdate->id},
                        from: \"2019-08-20 16:15:00\",
                        to: \"2019-08-20 17:00:00\",
                        network_type: LIVE
                    }
                ) {
                    id
                }
            }")->assertJsonPath('errors.0.message', "Can't have overlapping bookings for the same position!");
    }

    /** @test */
    public function testBookingUpdatedWhenTypesOverlapWithRelevantMessage()
    {
        factory(Booking::class)->create([
            'position_id' => $this->position->id,
            'from' => new Carbon('2019-08-20 15:00:00'),
            'to' => new Carbon('2019-08-20 16:30:00'),
            'network_type' => 1,
        ]);

        $bookingToUpdate = factory(Booking::class)->create([
            'position_id' => $this->position->id,
            'from' => new Carbon('2019-08-20 17:00:00'),
            'to' => new Carbon('2019-08-20 18:30:00'),
            'network_type' => 1,
        ]);

        // start
        $this->graphQL("
            mutation {
                updateBooking(
                    input: {
                        id: {$bookingToUpdate->id},
                        from: \"2019-08-20 16:15:00\",
                        to: \"2019-08-20 17:00:00\",
                        network_type: LIVE
                    }
                ) {
                    id,
                    network_type
                }
            }")->assertJson([
            'data' => [
                'updateBooking' => [
                    'id' => $bookingToUpdate->id,
                    'network_type' => Booking::NETWORK_TYPE_LIVE,
                ],
            ],
        ]);
    }

    /** @test */
    public function testBookingNotUpdatedWhenUpdatedTypeWithRelevantMessage()
    {
        factory(Booking::class)->create([
            'position_id' => $this->position->id,
            'from' => new Carbon('2019-08-20 15:00:00'),
            'to' => new Carbon('2019-08-20 16:30:00'),
            'network_type' => Booking::NETWORK_TYPE_LIVE,
        ]);

        $bookingToUpdate = factory(Booking::class)->create([
            'position_id' => $this->position->id,
            'from' => new Carbon('2019-08-20 17:00:00'),
            'to' => new Carbon('2019-08-20 18:30:00'),
            'network_type' => 1,
        ]);

        // start
        $this->graphQL("
            mutation {
                updateBooking(
                    input: {
                        id: {$bookingToUpdate->id},
                        from: \"2019-08-20 16:15:00\",
                        to: \"2019-08-20 17:00:00\",
                        network_type: LIVE
                    }
                ) {
                    id,
                    network_type
                }
            }")->assertJsonPath('errors.0.message', "Can't have overlapping bookings for the same position!");
    }

    /** @test */
    public function testValidationForBookingIdExistingOccurs()
    {
        $invalidBookingId = -1;

        $this->graphQL("
            mutation {
                updateBooking(
                    input: {
                        id: {$invalidBookingId},
                        from: \"2019-08-20 17:00:00\",
                        to: \"2019-08-20 18:00:00\",
                        network_type: LIVE
                    }
                ) {
                    id
                }
            }")->assertJson([
            'errors' => [
                [
                    'extensions' => [
                        'validation' => [
                            'input.id' => [
                                'The selected input.id is invalid.',
                            ],
                        ],
                        'category' => 'validation',
                    ],
                ],
            ],
        ]);
    }
}
