<?php

namespace Tests\Feature\Position;

use App\User;
use App\Modules\Position\Position;
use App\Modules\Position\TrainingPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class TrainingPositionSessionTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests, UserHelper;

    private $position;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->mockedUser(1457358, ['code' => 'C1', 'vatsim_id' => 4]));
        $this->mockUserFind();

        $this->traineeUser = factory(User::class)->create(['ratingCode' => 'S2']);

        $this->trainingPosition = factory(TrainingPosition::class)->create();
    }

    /** @test */
    public function testManagerCanGrantRightsToTrainee()
    {
        $this->graphQL("
        mutation {
            grantSessionRights(user_id: {$this->traineeUser->id}, training_position_id: {$this->trainingPosition->id}) {
                user {
                    id
                }
            }
        }")->assertJsonPath('data.assignPositionForTraining.user.id', $this->traineeUser->id);
    }

    /** @test */
    public function testManagerCanRevokeRightsFromTrainee()
    {
        $this->graphQL("
        mutation {
            revokeSessionRights(user_id: {$this->traineeUser->id}, training_position_id: {$this->trainingPosition->id}) {
                user {
                    id
                }
            }
        }")->assertJsonPath('data.assignPositionForTraining.user.id', $this->traineeUser->id);
    }

    /** @test */
    public function testManagerCannotGrantRightsAgain()
    {
        $this->graphQL("
        mutation {
            grantSessionRights(user_id: {$this->traineeUser->id}, training_position_id: {$this->trainingPosition->id}) {
                user {
                    id
                }
            }
        }")->assertJsonPath('errors.0.message', 'Rights have already been granted on this position to the user.')
            ->assertJsonPath('errors.0.extensions.code', 200);
    }

    /** @test */
    public function testManagerCannotRevokeRightsAgain()
    {
        $this->graphQL("
        mutation {
            revokeSessionRights(user_id: {$this->traineeUser->id}, training_position_id: {$this->trainingPosition->id}) {
                user {
                    id
                }
            }
        }")->assertJsonPath('errors.0.message', 'This user never had any rights on this position.')
            ->assertJsonPath('errors.0.extensions.code', 200);
    }

    /** @test */
    public function testTraineeRightsCanBeQueried()
    {
        $this->graphQL("
        mutation {
            grantSessionRights(user_id: {$this->traineeUser->id}, training_position_id: {$this->trainingPosition->id}) {
                user {
                    id
                }
            }
        }")->assertJsonPath('data.assignPositionForTraining.user.id', $this->traineeUser->id);

        $this->graphQL('
        query {
            positionsAvailableForTraining {
                position {
                    id,
                    users {
                        id
                    }
                }
            }
        }
        ')->assertJsonStructure(['data' => ['positionsAvailableForTraining']])
            ->assertJsonFragment([
                'data' => [
                    'positionsAvailableForTraining' => [
                        [
                            'position' => [
                                'id' => (string) $this->trainingPosition->position->id,
                            ],
                            'users' => [
                                [
                                    'id' => $this->traineeUser->id,
                                ],
                            ],
                        ],
                    ],
                ],
            ])
            ->assertStatus(200);
    }
}
