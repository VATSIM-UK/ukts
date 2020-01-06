<?php

namespace Tests\Feature\Position;

use App\Modules\Position\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class TrainingPositionTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests;

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
            markPositionForTraining(position_id: {$this->position->id} {
                position {
                    id
                }
            }
        }")->assertJsonPath('data.markPositionForTraining.position.id', $this->position->id);
    }

    /** @test */
    public function testUserCannotMarkPositionAsAvailableForTrainingWhenAlreadyAvailable()
    {

    }
}
