<?php


namespace Tests\Feature\Position;


use App\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class PositionTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests;

    /* @test */
    public function testItReturnsPositions()
    {
        $positions = factory(Position::class, 2)->create();

        $this->graphQL('
        {
            positions {
                id
                name
                frequency
            }
        }
        ')->assertJson([
            'data' => [
                'positions' => [
                    [
                        'id' => $positions->first()->id,
                        'name' => $positions->first()->name,
                        'frequency' => $positions->first()->frequency,
                    ],
                    [
                        'id' => $positions->last()->id,
                        'name' => $positions->last()->name,
                        'frequency' => $positions->last()->frequency,
                    ]
                ]
            ]
        ]);
    }

    /* @test */
    public function testItReturnsPositionById()
    {
        $positions = factory(Position::class, 2)->create();

        $this->graphQL("
        {
            position(id: {$positions->first()->id}) {
                id
                name
                frequency
            }
        }
        ")->assertJson([
            'data' => [
                'position' => [
                    'id' => $positions->first()->id,
                    'name' => $positions->first()->name,
                    'frequency' => $positions->first()->frequency,
                ]
            ]
        ]);
    }

    /* @test */
    public function testItReturnsPositionByCallsign()
    {
        $positions = factory(Position::class, 2)->create();

        $this->graphQL("
        {
            position(callsign: \"{$positions->first()->callsign}\") {
                id
                name
                frequency
            }
        }
        ")->assertJson([
            'data' => [
                'position' => [
                    'id' => $positions->first()->id,
                    'name' => $positions->first()->name,
                    'frequency' => $positions->first()->frequency,
                ]
            ]
        ]);
    }

    /** @test */
    public function testValidPositionCanBeCreated()
    {
        $this->graphQL('
          mutation { 
            createPosition(name: "Bristol Tower", callsign: "EGGD_TWR", frequency: "133.850", type: 4) {
                id
                name
            }
          }')->assertJsonStructure([
            'data' => [
                'createPosition' => [
                    'id',
                    'name'
                ]
            ]
        ]);

        $this->assertDatabaseHas('positions', [
            'name' => 'Bristol Tower',
            'callsign' => 'EGGD_TWR',
            'frequency' => '133.850'
        ]);
    }

    /** @test */
    public function testDuplicatePositionCannotBeCreated()
    {
        factory(Position::class)->create([
            'name' => 'Bristol Tower',
            'callsign' => 'EGGD_TWR',
            'frequency' => '133.850'
        ]);

        $this->graphQL('
            mutation { 
                createPosition(name: "Bristol Tower", callsign: "EGGD_TWR", frequency: "133.850", type: 4) {
                    id
                    name
                }
            }')->assertJsonFragment(["validation" => [
                "callsign" => [
                    "The callsign has already been taken."
                ]
            ]]);
    }
}
