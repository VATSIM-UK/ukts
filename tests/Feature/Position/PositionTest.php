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
}
