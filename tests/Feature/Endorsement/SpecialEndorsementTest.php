<?php

namespace Tests\Feature\Endorsement;

use App\Modules\Endorsement\Special\Assignment;
use App\Modules\Endorsement\Special\SpecialEndorsement;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class SpecialEndorsementTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests;

    protected $user;

    protected $endorsement;

    protected function setUp(): void
    {
        parent::setUp();

        $this->endorsement = factory(SpecialEndorsement::class)->create();

        $this->user = factory(User::class)->make();
    }

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
                        'name',
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function testAllEndorsementCanBeRetrievedByID()
    {
        $endorsement = factory(SpecialEndorsement::class)->create([
            'name' => 'FISO Operator',
        ]);

        $this->graphQL("
        query {
          specialEndorsement(id: \"{$endorsement->id}\") {
            id
            name
          }
        }")->assertJson([
            'data' => [
                'specialEndorsement' => [
                    'id' => $endorsement->id,
                    'name' => $endorsement->name,
                ],
            ],
        ]);
    }

    /** @test */
    public function testEndorsementCanBeCreated()
    {
        $this->graphQL('
            mutation {
                createSpecialEndorsement(
                    input: {
                        name: "Heathrow Director"
                    }
                )
                {
                    id
                    name
                }
            }
        ');

        $this->assertDatabaseHas('special_endorsements', [
            'name' => 'Heathrow Director',
        ]);
    }

    /** @test */
    public function testEndorsementCanBeUpdated()
    {
        $endorsement = factory(SpecialEndorsement::class)->create([
            'name' => 'FISO Operator',
        ]);

        $this->assertDatabaseHas('special_endorsements', [
            'id' => $endorsement->id,
            'name' => 'FISO Operator',
        ]);

        $this->graphQL("
            mutation {
                updateSpecialEndorsement(
                    id: \"{$endorsement->id}\"
                    input: {
                        name: \"Heathrow Director\"
                    }
                )
                {
                    id
                    name
                }
            }
        ");

        $this->assertDatabaseHas('special_endorsements', [
            'id' => $endorsement->id,
            'name' => 'Heathrow Director',
        ]);
    }

    /** @test */
    public function testEndorsementCanBeSoftDeleted()
    {
        Carbon::setTestNow(Carbon::now());

        $endorsement = factory(SpecialEndorsement::class)->create();

        $this->assertDatabaseHas('special_endorsements', [
            'id' => $endorsement->id,
            'name' => $endorsement->name,
            'deleted_at' => null,
        ]);

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
            'deleted_at' => now(),
        ]);
    }

    /** @test */
    public function testEndorsementsCanBeRetrievedByUser()
    {
        $assignment = Assignment::create([
            'user_id' => 1300005,
            'granted_by' => 1300005,
            'endorsement_id' => $this->endorsement->id,
        ]);

        $this->mock(User::class, function ($mock) {
            $mock
                ->shouldReceive('find')
                ->andReturn(User::initModelWithData(['id' => 1300005]));
        });

        $this->graphQL('
        query {
            specialEndorsementsForUser(user_id: 1300005) {
                id
                name
                pivot {
                    user_id
                    created_at
                }
            }
        }
        ')->assertJson([
            'data' => [
                'specialEndorsementsForUser' => [
                    [
                        'id' => $this->endorsement->id,
                        'name' => $this->endorsement->name,
                        'pivot' => [
                            'user_id' => $assignment->user_id,
                            'created_at' => $assignment->created_at,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function testListOfEndorsedUsersCanBeRetrieved()
    {
        $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('findMany')
                ->andReturn(collect([
                    User::initModelWithData([
                        'id' => $this->user->id,
                        'name_first' => $this->user->name_first,
                    ])
                ]));

            $mock->shouldReceive('initModelWithData')
                ->andReturn(
                    User::initModelWithData([
                        'id' => $this->user->id,
                        'name_first' => $this->user->name_first,
                    ])
            );
        })->makePartial();

        $assignment = Assignment::create([
            'user_id' => $this->user->id,
            'granted_by' => $this->user->id,
            'endorsement_id' => $this->endorsement->id,
        ]);

        $this->graphQL("
        query {
          specialEndorsement(id: {$this->endorsement->id}) {
            name
            users {
                name_first
            }
          }
        }")->assertJsonPath('data.specialEndorsement.name', $this->endorsement->name)
            ->assertJsonPath('data.specialEndorsement.users.0.name_first', $this->user->name_first);
    }
}
