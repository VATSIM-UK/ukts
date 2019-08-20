<?php


namespace Tests\Feature\Position;


use App\Booking;
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
            bookings (count: 10) {
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
                    'data' => [[
                        'id',
                        'position' => [
                            'id',
                            'callsign'
                        ]
                    ]],
                    'paginatorInfo' => [
                        'currentPage',
                        'lastPage'
                    ]
                ]
            ]
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
                        'callsign' => $bookings->get(1)->position->callsign
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function testValidBookingCanBeCreated()
    {
        factory(Position::class)->create();

        $data = $this->graphQL('
          mutation { 
            createBooking(user_id: 1300001, position: {
                connect: 1
            }, from:"2019-08-20 15:00:00", to:"2019-08-20 16:30:00") {
                id
            }
          }');

        $this->assertDatabaseHas('bookings', [
            'id' => $data->json('data.createBooking.id'),
            'user_id' => 1300001,
            'position_id' => 1,
            'from' => new Carbon("2019-08-20 15:00:00"),
            'to' => new Carbon("2019-08-20 16:30:00"),
        ]);
    }

    /** @test */
    public function testInvalidBookingCantBeCreated()
    {
        factory(Position::class)->create();

        $this->graphQL('
          mutation { 
            createBooking(user_id: 1300001, position: {
                connect: 1
            }, from:"Blah", to:"2019-08-20 16:30:00") {
                id
            }
          }')->assertJsonStructure([
                "errors" => [
                    [
                        "message",
                        "extensions",
                        "locations"
                    ]
                ]
            ]
        );
    }

    /** @test */
    public function testItCanUpdateABooking()
    {
        factory(Booking::class)->create([
            'id' => 1,
            'user_id' => 1300001,
            'position_id' => 1,
            'from' => new Carbon("2019-08-20 15:00:00"),
            'to' => new Carbon("2019-08-20 16:30:00"),
        ]);

        $this->graphQL('
            mutation { 
                updateBooking(id: 1, to: "2019-08-20 17:00:00") {
                    id
                }
            }')->assertJson([
            'data' => [
                'updateBooking' => [
                    'id' => 1
                ]
            ]
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => 1,
            'to' => new Carbon("2019-08-20 17:00:00")
        ]);
    }
}