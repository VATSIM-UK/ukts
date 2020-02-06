<?php

namespace Tests\Feature\Availability;

use App\Modules\Availability\Availability;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;
use Tests\Unit\Availability\AvailabilityTestHelper;

class AvailabilityUpdateTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests, AvailabilityTestHelper;

    private $position;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->mockedUser());
        $this->mockUserFind();

        $this->mockUserModel = User::initModelWithData([
            'id' => 1234567,
            'name_fist' => 'First',
            'name_last' => 'Last',
        ]);

    }

    /** @test */
    public function testItCanUpdateAvailability()
    {

        $from = new Carbon();
        $from->addHours(2);
        $to = new Carbon();
        $to->addHours(3);

        factory(Availability::class)->create([
            'id' => 1,
            'user_id' => $this->mockUserModel->id,
            'from' => $from->toDateTimeString(),
            'to' => $to->toDateTimeString(),
        ]);

        $this->graphQL("
            mutation {
                updateAvailability(
                    input: {
                        id: 1,
                        from: \"{$from->toIso8601String()}\",
                        to: \"{$to->toIso8601String()}\",
                    }
                ) {
                    id
                }
            }")->assertJson([
            'data' => [
                'updateAvailability' => [
                    'id' => 1,
                ],
            ],
        ]);

        $this->assertDatabaseHas('availability', [
            'id' => 1,
            'from' => $from,
            'to' => $to,
        ]);
    }

    /** @test */
    public function testAvailabilityNotUpdatedWhenOverlapsWithRelevantMessage()
    {
        $from1 = new Carbon();
        $from1->addHours(2);

        $to1 = new Carbon();
        $to1->addHours(3);

        $from2 = $from1->copy()->subHour();
        $to2 = $to1->copy()->addHour();

        $fromReplacement = $from1->copy()->addMinutes(20);
        $toReplacement = $to1->copy()->addminutes(30);

        factory(Availability::class)->create([
            'from' => $from1,
            'to' => $to1,
            'user_id' => $this->mockUserModel->id,
        ]);

        $availabilityToUpdate = factory(Availability::class)->create([
            'from' => $from2,
            'to' => $to2,
            'user_id' => $this->mockUserModel->id,
        ]);

        // start
        $this->graphQL("
            mutation {
                updateAvailability(
                    input: {
                        id: {$availabilityToUpdate->id},
                        from: \"{$fromReplacement->toIso8601String()}\",
                        to: \"{$toReplacement->toIso8601String()}\"
                    }
                ) {
                    id
                }
            }")->assertJsonPath('errors.0.message', "Can't have overlapping availability!");
    }

    /** @test */
    public function testValidationForAvailabilityIdExistingOccurs()
    {
        $invalidAvailabilityId = -1;

        $from = new Carbon();
        $from->addHours(2);

        $to = new Carbon();
        $to->addHours(3);

        $this->graphQL("
            mutation {
                updateAvailability(
                    input: {
                        id: {$invalidAvailabilityId},
                        from: \"{$from->toIso8601String()}\",
                        to: \"{$to->toIso8601String()}\",
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

    /** @test */
    public function cannotUpdateSomebodyElsesAvailability()
    {
        $from1 = new Carbon();
        $from1->addHours(2);

        $to1 = new Carbon();
        $to1->addHours(3);

        $from2 = $from1->copy()->subHour();
        $to2 = $to1->copy()->addHour();

        factory(Availability::class)->create([
            'from' => $from1,
            'to' => $to1,
            'user_id' => 999110,
            'id' => 118839
        ]);

        // start
        $this->graphQL("
            mutation {
                updateAvailability(
                    input: {
                        id: 118839,
                        from: \"{$from1->toIso8601String()}\",
                        to: \"{$to1->toIso8601String()}\"
                    }
                ) {
                    id
                }
            }")->assertJsonPath('errors.0.message', "You do not have permission to update this availability");
    }
}
