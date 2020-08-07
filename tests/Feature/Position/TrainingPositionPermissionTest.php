<?php

namespace Tests\Feature\Position;

use App\Modules\Position\TrainingPosition;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class TrainingPositionPermissionTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests, UserHelper;

    private $position;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->mockedUser());
        $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('findOrFail')
                ->andReturn(
                    User::initModelWithData([
                        'id' => $this->mockUserId,
                        'name_first' => 'First',
                    ])
                );
            $mock->shouldReceive('initModelWithData')
            ->andReturn(
                User::initModelWithData([
                    'id' => $this->mockUserId,
                    'name_first' => 'Last',
                ])
            );
        })->makePartial();

        $this->trainingPosition = factory(TrainingPosition::class)->create();
    }

    /** @test */
    public function testManagerCanGrantRightsToTrainee()
    {
        $this->graphQL("
        mutation {
            grantPermissions(user_id: {$this->mockUserId}, training_position_id: {$this->trainingPosition->id})
        }")->assertJson(['data.grantPermissions', true])
            ->assertStatus(200);
    }

    /** @test */
    public function testManagerCanRevokeRightsFromTrainee()
    {
        $this->graphQL("
        mutation {
            revokePermissions(user_id: {$this->mockUserId}, training_position_id: {$this->trainingPosition->id})
        }")->assertJsonPath('data.revokePermissions', true)
            ->assertStatus(200);
    }

    /** @test */
    public function testManagerCannotGrantRightsAgain()
    {
        $this->graphQL("
        mutation {
            grantPermissions(user_id: {$this->mockUserId}, training_position_id: {$this->trainingPosition->id})
        }")->assertJsonPath('errors.0.message', 'Rights have already been granted on this position to the user.')
            ->assertJsonPath('errors.0.extensions.code', 200);
    }

    /** @test */
    public function testManagerCannotRevokeRightsAgain()
    {
        $this->graphQL("
        mutation {
            revokePermissions(user_id: {$this->mockUserId}, training_position_id: {$this->trainingPosition->id})
        }")->assertJsonPath('errors.0.message', 'This user never had any rights on this position.')
            ->assertJsonPath('errors.0.extensions.code', 200);
    }

    /** @test */
    public function testTraineeRightsCanBeQueried()
    {
        $this->graphQL("
        mutation {
            grantPermissions(user_id: {$this->mockUserId}, training_position_id: {$this->trainingPosition->id})
        }")->assertJsonPath('data.assignPositionForTraining.user.id', $this->mockUserId);

        $this->graphQL("
        query {
            studentsTrainingOnPosition(id: {$this->trainingPosition->id}) {
                users {
                    id
                }
            }
        }
        ")->assertJsonStructure(['data' => ['positionsAvailableForTraining']])
            ->assertJsonFragment([
                'data' => [
                    'studentsTrainingOnPosition' => [
                        [
                            'users' => [
                                [
                                    'id' => $this->mockUserId,
                                ],
                            ],
                        ],
                    ],
                ],
            ])
            ->assertStatus(200);
    }
}
