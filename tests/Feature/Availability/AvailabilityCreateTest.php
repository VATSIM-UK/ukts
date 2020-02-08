<?php

namespace Tests\Feature\Availability;

use App\Modules\Availability\Availability;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;
use Tests\Unit\Availability\AvailabilityTestHelper;

class AvailabilityCreateTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests, AvailabilityTestHelper;


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
        $from->addHour();
        $to = $from->clone()->addHour();

        // Create two availabilities
        factory(Availability::class)->create([
            'from' => $from->toDateTimeString(),
            'to' => $to->toDateTimeString(),
            'user_id' => $this->mockUserId,
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
    public function testMustBeOverMinTime()
    {

        $from = new Carbon();
        $from->addHour();
        $to = $from->clone()->addMinutes(29);

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
          }")->assertJsonPath('errors.0.message', "The minimum availability time is 30 minutes");
    }
    /** @test */
    public function testOverlappingAvailabilitysCannotBeCreatedOverlappingOnStart()
    {

        $from = new Carbon();
        $from->addHour();
        $to = $from->clone()->addHour();

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
    public function testItCanDeleteAvailability()
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
