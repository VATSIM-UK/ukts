<?php

namespace Tests\Unit\Position;

use App\Modules\Endorsement\Solo\SoloEndorsement;
use App\Modules\Endorsement\Special\SpecialEndorsement;
use App\Modules\Position\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $position;

    protected function setUp(): void
    {
        parent::setUp();

        $this->position = factory(Position::class)->create(['callsign' => 'EGGD_APP']);
    }

    /** @test */
    public function itReturnsTheCorrectSuffixAttribute()
    {
        $this->assertEquals('APP', $this->position->suffix);
    }

    /** @test */
    public function itReturnsTheCorrectSuffixWithSubPositions()
    {
        $subPosition = factory(Position::class)->make(['callsign' => 'EGGD_R_APP']);

        $this->assertEquals('APP', $subPosition->suffix);
    }

    /** @test */
    public function itCanBeRelatedToSpecialEndorsements()
    {
        $endorsements = factory(SpecialEndorsement::class, 2)->create();

        $this->assertEquals(0, $this->position->specialEndorsements()->count());

        $this->position->specialEndorsements()->attach($endorsements->last());

        $this->assertEquals(1, $this->position->specialEndorsements()->count());
        $this->assertEquals($endorsements->last()->name, $this->position->specialEndorsements()->first()->name);
    }

    /** @test */
    public function itCanBeRelatedToSoloEndorsements()
    {
        factory(SoloEndorsement::class, 3)->create([
            'position_id' => $this->position->id,
            'user_id' => 134566
        ]);

        $this->assertCount(3, $this->position->soloEndorsements);
    }

}
