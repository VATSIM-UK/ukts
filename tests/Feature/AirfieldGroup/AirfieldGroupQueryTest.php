<?php

namespace Tests\Feature\AirfieldGroup;

use App\Modules\Airfield\Airfield;
use App\Modules\AirfieldGroup\AirfieldGroup;
use App\Modules\Position\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class AirfieldGroupQueryTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests;

    /** @test */
    public function testCanQueryForAllAirfieldGroups()
    {
        $airfieldGroups = factory(AirfieldGroup::class, 2)->create();

        $this->graphQL('
        query {
            airfieldGroups {
                name
            }
        }
        ')->assertJsonPath('data.airfieldGroups.0.name', $airfieldGroups[0]->name)
            ->assertJsonPath('data.airfieldGroups.1.name', $airfieldGroups[1]->name);
    }

    /** @test */
    public function testCanQueryForAirfieldsContainedWithinTrainingGroup()
    {
        $airfieldGroup = factory(AirfieldGroup::class)->create();
        $airfield = factory(Airfield::class)->create(['airfield_group_id' => $airfieldGroup->id]);

        $this->graphQL("
        query {
            airfieldGroup(id: {$airfieldGroup->id}) {
                airfields {
                    icao
                    iata
                    display_name
                }
            }
        }
        ")->assertJsonPath('data.airfieldGroup.airfields.0', $airfield->only('icao', 'iata', 'display_name'));
    }

    /** @test */
    public function testCanQueryForPositionsContainedWithinTrainingGroup()
    {
        $airfieldGroup = factory(AirfieldGroup::class)->create();
        $airfield = factory(Airfield::class)->create(['airfield_group_id' => $airfieldGroup->id]);
        $position = factory(Position::class)->create(['airfield_icao' => $airfield->icao]);

        $this->graphQL("
        query {
            airfieldGroup(id: {$airfieldGroup->id}) {
                positions {
                    name
                    callsign
                    frequency
                }
            }
        }
        ")->assertJsonPath('data.airfieldGroup.positions.0', $position->only('callsign', 'frequency', 'name'));
    }

    /** @test */
    public function testCanAssociateAnAirfieldWithAirfieldGroup()
    {
        $airfieldGroup = factory(AirfieldGroup::class)->create();
        $airfield = factory(Airfield::class)->create();

        $this->graphQL("
            mutation {
                addAirfieldToGroup(
                    input: {
                        icao: \"{$airfield->icao}\",
                        airfieldGroup: {$airfieldGroup->id}
                    }
                ) {
                    airfieldGroup {
                        name
                    }
                }
            }
        ")->assertJsonPath('data.addAirfieldToGroup.airfieldGroup.name', $airfieldGroup->name);

        $this->assertDatabaseHas('airfields',
            array_merge($airfield->toArray(), ['airfield_group_id' => $airfieldGroup->id]));
    }

    /** @test */
    public function testCantAssociateWithGroupThatDoesntExist()
    {
        $airfieldGroup = 999;
        $airfield = factory(Airfield::class)->create();

        $this->graphQL("
            mutation {
                addAirfieldToGroup(
                    input: {
                        icao: \"{$airfield->icao}\",
                        airfieldGroup: {$airfieldGroup}
                    }
                ) {
                    airfieldGroup {
                        name
                    }
                }
            }
        ");

        $this->assertDatabaseHas('airfields',
            array_merge($airfield->toArray(), ['airfield_group_id' => null]));
    }

    /** @test */
    public function testThrowsErrorWhenAirfieldIsAlreadyAssignedToGroup()
    {
        $airfieldGroup = factory(AirfieldGroup::class)->create();
        $airfield = factory(Airfield::class)->create(['airfield_group_id' => $airfieldGroup->id]);

        $this->graphQL("
            mutation {
                addAirfieldToGroup(
                    input: {
                        icao: \"{$airfield->icao}\",
                        airfieldGroup: {$airfieldGroup->id}
                    }
                )
                {
                    airfieldGroup {
                        name
                    }
                }
            }")->assertJsonPath('errors.0.message', 'The given airfield already has an AirfieldGroup assigned.');
    }
}
