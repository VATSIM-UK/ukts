<?php

namespace Tests\Unit\AirfieldGroup;

use App\Modules\Airfield\Airfield;
use App\Modules\AirfieldGroup\AirfieldGroup;
use App\Modules\Position\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AirfieldGroupTest extends TestCase
{
    use RefreshDatabase;

    private $airfieldGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->airfieldGroup = factory(AirfieldGroup::class)->create();
    }

    /** @test */
    public function itCanBeRelatedToManyAirfields()
    {
        factory(Airfield::class)->create(['airfield_group_id' => $this->airfieldGroup->id]);

        $this->airfieldGroup->airfields->each(function ($airfield) {
            $this->assertInstanceOf(Airfield::class, $airfield);
        });
    }

    /** @test */
    public function itCanQueryForRelatedPositionsThroughTheAirfieldModel()
    {
        $airfield = factory(Airfield::class)->create(['airfield_group_id' => $this->airfieldGroup->id]);
        factory(Position::class, 3)->create(['airfield_icao' => $airfield->icao]);

        $this->airfieldGroup->positions->each(function ($position) {
            $this->assertInstanceOf(Position::class, $position);
        });
    }
}
