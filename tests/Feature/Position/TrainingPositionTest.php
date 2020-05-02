<?php

namespace Tests\Feature\Position;

use App\Modules\Position\Position;
use App\Modules\Position\TrainingPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class TrainingPositionTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests, WithoutMiddleware;

    private $position;

    protected function setUp(): void
    {
        parent::setUp();

        $this->position = factory(Position::class)->create();
    }

    /** @test */
    public function testUserCanMarkPositionAsAvailableForTraining()
    {
        $this->graphQL("
        mutation {
            assignPositionForTraining(position_id: {$this->position->id}) {
                position {
                    id
                }
            }
        }")->assertJsonPath('data.assignPositionForTraining.position.id', $this->position->id);
    }

    /** @test */
    public function testUserCannotMarkPositionAsAvailableForTrainingWhenAlreadyAvailable()
    {
        factory(TrainingPosition::class)->create(['position_id' => $this->position->id]);

        $this->graphQL("
        mutation {
            assignPositionForTraining(position_id: {$this->position->id}) {
                position {
                    id
                }
            }
        }")->assertJsonPath('errors.0.message', 'The given position is already assigned for training.')
            ->assertJsonPath('errors.0.extensions.code', 422);
    }

    /** @test */
    public function testTrainingPositionAssignmentsCanBeQueried()
    {
        factory(TrainingPosition::class)->create(['position_id' => $this->position->id]);
        $this->graphQL('
        query {
            positionsAvailableForTraining {
                position {
                    id
                }
            }
        }
        ')->assertJsonStructure(['data' => ['positionsAvailableForTraining']])
            ->assertJsonFragment([
                'data' => [
                    'positionsAvailableForTraining' => [
                        [
                            'position' => [
                                'id' => (string) $this->position->id,
                            ],
                        ],
                    ],
                ],
            ])
            ->assertStatus(200);
    }

    /** @test */
    public function testTrainingPositionAssignmentsCanBeRevoked()
    {
        factory(TrainingPosition::class)->create(['position_id' => $this->position->id]);
        $this->graphQL("
            mutation {
                removePositionFromTraining(position_id: {$this->position->id})
            }
        ")->assertJsonPath('data.removePositionFromTraining', true)
            ->assertStatus(200);
    }

    /** @test */
    public function testExceptionThrownWhenAssignmentIsRemovedThatDoesntExist()
    {
        $this->graphQL("
            mutation {
                removePositionFromTraining(position_id: {$this->position->id})
            }
        ")->assertJsonPath(
            'errors.0.message',
            'The given position has not been assigned for training so cannot be deleted.'
        )
            ->assertStatus(200);
    }
}
