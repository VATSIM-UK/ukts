<?php

namespace Tests\Unit\Airfield;

use App\Modules\Airfield\Airfield;
use App\Modules\Position\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AirfieldUnitTest extends TestCase
{
    use RefreshDatabase;

    private $airfield;

    protected function setUp(): void
    {
        parent::setUp();

        $this->airfield = factory(Airfield::class)->create();
    }

    /** @test */
    public function itCanBeAssociatedWithOneOrManyPositions()
    {
        factory(Position::class, 3)->create(['airfield_icao' => $this->airfield->icao]);

        $this->airfield->positions->each(function ($position) {
            $this->assertInstanceOf(Position::class, $position);
        });
    }

    /** @test */
    public function itHasTheICAOAsPrimaryKey()
    {
        $icao = 'EGGD';
        factory(Airfield::class)->create(['icao' => $icao]);

        $this->assertEquals($icao, Airfield::findOrFail($icao)->icao);
    }
}
