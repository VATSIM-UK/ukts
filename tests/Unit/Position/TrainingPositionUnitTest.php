<?php

namespace Tests\Unit\Position;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingPositionUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(TrainingPositionServiceInterface::class);
    }

    /** @test */
    public function itPassesCheckWhenNoActiveAssignmentsForGivenPositionExist()
    {
        $this->assertTrue($this->service->checkExistingActivePositionAssignments($this->position->id));
    }


    /** @test */
    public function itDetectsWhenActivePositionAssignmentExists()
    {
        $this->service->checkExistingPositionAssignments($this->position->id);
    }
}
