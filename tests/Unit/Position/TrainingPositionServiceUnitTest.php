<?php

namespace Tests\Unit\Position;

use App\Modules\Position\Position;
use App\Modules\Position\PositionAlreadyAssignedForTrainingException;
use App\Modules\Position\TrainingPositionAssignment;
use App\Modules\Position\TrainingPositionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingPositionServiceUnitTest extends TestCase
{
    use RefreshDatabase;

    private $service;
    private $position;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(TrainingPositionService::class);

        $this->position = factory(Position::class)->create();
    }

    /** @test */
    public function itPassesCheckWhenNoActiveAssignmentsForGivenPositionExist()
    {
        $this->assertTrue($this->service->checkExistingActivePositionAssignments($this->position));
    }

    /** @test */
    public function itDetectsWhenActivePositionAssignmentExists()
    {
        factory(TrainingPositionAssignment::class)->create(['position_id' => $this->position->id]);
        $this->assertFalse($this->service->checkExistingActivePositionAssignments($this->position));
    }

    /** @test */
    public function itPassesCheckIfPreviousAssignmentHasBeenSoftDeleted()
    {
        $assignment = factory(TrainingPositionAssignment::class)->create(['position_id' => $this->position->id]);
        $assignment->delete();

        $this->assertTrue($this->service->checkExistingActivePositionAssignments($this->position));
    }

    /** @test */
    public function itCreatesAssignmentGivenValidConditions()
    {
        $this->service->createAssignment($this->position);

        $this->assertDatabaseHas('training_position_assignments', [
            'position_id' => $this->position->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function itDoesntCreateAndThrowsExceptionWhenExistingAssignmentExists()
    {
        factory(TrainingPositionAssignment::class)->create(['position_id' => $this->position->id]);
        $this->expectException(PositionAlreadyAssignedForTrainingException::class);

        $this->service->createAssignment($this->position);

        $this->assertDatabaseMissing('training_position_assignments', [
            'position_id' => $this->position->id,
            'deleted_at' => null,
        ]);
    }
}
