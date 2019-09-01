<?php

namespace Tests\Feature\Endorsement;

use App\SpecialEndorsement;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class SpecialEndorsementTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests;

    /** @test */
    public function testAllEndorsementsCanBeListed()
    {
        factory(SpecialEndorsement::class, 5)->create();

        $this->graphQL('
        query {
          specialEndorsements {
            id
            name
          }
        }')->assertJsonStructure([
            'data' => [
                'specialEndorsements' => [
                    [
                        'id',
                        'name'
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function testEndorsementCanBeCreated()
    {
        $this->graphQL('
            mutation { 
                createSpecialEndorsement(
                    name: "Heathrow Director"
                )
                {
                    id
                    name
                }                    
            }
        ');

        $this->assertDatabaseHas('special_endorsements', [
            'name' => 'Heathrow Director'
        ]);
    }

    /** @test */
    public function testEndorsementCanBeSoftDeleted()
    {
        Carbon::setTestNow(Carbon::now());

        $endorsement = factory(SpecialEndorsement::class)->create();

        $this->graphQL("
            mutation {
                deleteSpecialEndorsement(id: \"{$endorsement->id}\") {
                    id
                    name
                }
            }
        ");

        $this->assertDatabaseHas('special_endorsements', [
            'id' => $endorsement->id,
            'name' => $endorsement->name,
            'deleted_at' => now()
        ]);
    }
}
