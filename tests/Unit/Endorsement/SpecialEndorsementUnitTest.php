<?php

namespace Tests\Unit\Endorsement;

use App\Modules\Endorsement\Special\SpecialEndorsement;
use App\Modules\Position\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpecialEndorsementUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function itCanBeRelatedToPositions()
    {
        $positions = factory(Position::class, 2)->create();
        $endorsement = factory(SpecialEndorsement::class)->create();

        $this->assertEquals(0, $endorsement->positions()->count());

        $endorsement->positions()->attach($positions->last());

        $this->assertEquals(1, $endorsement->positions()->count());
        $this->assertEquals($positions->last()->callsign, $endorsement->positions()->first()->callsign);
    }
}
