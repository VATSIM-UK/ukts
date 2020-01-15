<?php

namespace Tests\Unit\Position;

use App\Modules\Position\Position;
use App\Modules\Position\TrainingPositionAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingPositionAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected $trainingAssignment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trainingAssignment = factory(TrainingPositionAssignment::class)->create();
    }

    /** @test */
    public function itHasRelationToAPosition()
    {
        $this->assertInstanceOf(Position::class, $this->trainingAssignment->position);
    }
}
