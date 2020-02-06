<?php

namespace Tests\Feature\Availability;

use App\Modules\Availability\Availability;
use App\Modules\Endorsement\Special\Assignment;
use App\Modules\Endorsement\Special\SpecialEndorsement;
use App\Modules\Position\Position;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;
use Tests\Unit\Availability\AvailabilityTestHelper;

class AvailabilityCreateTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests, AvailabilityTestHelper;

    protected $position;
    public $expectedExceptions = [
        'errors' => [
            [
                'message',
                'extensions',
                'locations',
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->mockedUser());
        $this->mockUserFind();

        $this->position = factory(Position::class)->create();

    }

    private function sendCreateCall(Carbon $from, Carbon $to)
    {
        return $this->graphQL("
          mutation {
            createAvailability(
                input: {
                    from:\"{$from->toIso8601String()}\",
                    to:\"{$to->toIso8601String()}\"
                }
            )
            {
                id
            }
          }");
    }

    /** @test */
    public function testValidAvailabilityCanBeCreated()
    {
        $this->withoutExceptionHandling();
        $creationJsonFormat = ['data' => ['createAvailability' => ['id']]];
        $from = new Carbon();
        $from->addHours(2);
        $to = new Carbon();
        $to->addHours(3);
        $this->sendCreateCall($from, $to)->assertJsonStructure($creationJsonFormat);

        $this->assertDatabaseHas('availability', [
            'user_id' => $this->mockUserId,
            'from' => $from->toDateTimeString(),
            'to' => $to->toDateTimeString(),
        ]);

    }

    /** @test */
    public function fromDateCannotBeInThePast()
    {

        $from = new Carbon();
        $from->subHour();
        $to = new Carbon();
        $to->addHour();

        $this->sendCreateCall($from, $to)->assertJsonStructure($this->expectedExceptions);
    }

    /** @test */
    public function toDateCannotBeBeforeFromDate()
    {

        $from = new Carbon();
        $from->addHours(2);
        $to = new Carbon();
        $to->addHours(1);

        $this->sendCreateCall($from, $to)->assertJsonStructure($this->expectedExceptions);
    }

    /** @test */
    public function toFromDateCannotBeEqual()
    {

        $from = new Carbon();
        $from->addHours(2);
        $to = new Carbon();
        $to->addHours(2);

        $this->sendCreateCall($from, $to)->assertJsonStructure($this->expectedExceptions);
    }

    /** @test */
    public function typesMustBeCorrect()
    {
        $to = new Carbon();
        $to->addHour();
        // Invalid "from" date
        $this->graphQL("
          mutation {
            createAvailability(
                input: {
                    from:'Blah',
                    to: '{$to->toIso8601String()}'
                }
            )
            {
                id
            }
          }")->assertJsonStructure($this->expectedExceptions);
    }

    /** @test */
    public function testOverlappingAvailabilitysCannotBeCreatedOverlappingOnEnd()
    {

        $from = new Carbon();
        $to = new Carbon();
        $to->addHour();

        // Create two availabilitys for position 1
        factory(Availability::class)->create([
            'from' => $from->toDateTimeString(),
            'to' => $to->toDateTimeString(),
        ]);

        // Test 1: Doesn't allow to book inside of availability
        $this->graphQL("
          mutation {
            createAvailability(
                input: {
                  from:\"{$from->toIso8601String()}\",
                  to:\"{$to->toIso8601String()}\"
                }
            )
            {
                id
            }
          }")->assertJsonPath('errors.0.message', "Can't have overlapping availability!");
    }

    /** @test */
    public function testOverlappingAvailabilitysCannotBeCreatedOverlappingOnStart()
    {

        $from = new Carbon();
        $to = new Carbon();
        $to->addHour();

        factory(Availability::class)->create([
            'user_id' => $this->mockUserId,
            'from' => $from->toDateTimeString(),
            'to' => $to->toDateTimeString(),
        ]);


        $this->graphQL("
          mutation {
            createAvailability(
                input: {
                  from:\"{$from->toIso8601String()}\",
                    to:\"{$to->toIso8601String()}\"
                }
            )
            {
                id
            }
          }")->assertJsonPath('errors.0.message', "Can't have overlapping availability!");
    }

    /** @test */
    public function testItCanDeleteAAvailability()
    {
        factory(Availability::class)->create();

        $this->assertEquals(1, Availability::count());

        $this->graphQL('
            mutation {
                deleteAvailability(id: 1) {
                    id
                }
            }')->assertJson([
            'data' => [
                'deleteAvailability' => [
                    'id' => 1,
                ],
            ],
        ]);

        $this->assertEquals(0, Availability::count());
    }
}
