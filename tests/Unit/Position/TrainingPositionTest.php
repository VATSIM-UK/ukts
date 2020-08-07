<?php

namespace Tests\Unit\Position;

use App\Modules\Position\Position;
use App\Modules\Position\TrainingPosition;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingPositionTest extends TestCase
{
    use RefreshDatabase;

    protected $trainingAssignment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trainingAssignment = factory(TrainingPosition::class)->create();
    }

    /** @test */
    public function itHasRelationToAPosition()
    {
        $this->assertInstanceOf(Position::class, $this->trainingAssignment->position);
    }

    /** @test */
    public function itHasRelationToStudents()
    {
        $this->assertContainsOnlyInstancesOf(User::class, $this->trainingAssignment->students);
    }

    /** @test */
    public function itHasRelationToMentors()
    {
        $this->assertContainsOnlyInstancesOf(User::class, $this->trainingAssignment->mentors);
    }
}
