<?php

namespace Tests\Feature\Endorsement;

use App\Modules\Endorsement\Special\SpecialEndorsement;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class SpecialEndorsementRequestTest extends TestCase
{
    use MakesGraphQLRequests, RefreshDatabase;

    protected $endorsement;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->endorsement = factory(SpecialEndorsement::class)->create();

        $this->user = factory(User::class)->make();
    }

    /** @test */
    public function testRequestCanBeMadeAgainstAnEndorsement()
    {
        $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('find')
                ->andReturn(
                    User::initModelWithData([
                        'id' => 1300005,
                        'name_first' => 'Callum',
                    ])
                );
            $mock->shouldReceive('findMany')
                ->andReturn(
                    collect([
                        User::initModelWithData([
                            'id' => 1300005,
                            'name_first' => 'Callum',
                        ]),
                    ])
                );
        })->makePartial();

        $this->graphQL("
        mutation {
            requestSpecialEndorsement (
                input: {
                    user_id: 1300005,
                    endorsement_id: {$this->endorsement->id},
                    requested_by: 1300002
                }
            ) {
                approved_at
                denied_at
                user {
                    name_first
                }
            }
        }
        ")->assertJsonPath('data.requestSpecialEndorsement.approved_at', null)
            ->assertJsonPath('data.requestSpecialEndorsement.denied_at', null)
            ->assertJsonPath('data.requestSpecialEndorsement.user.name_first', 'Callum');
    }

    /** @test */
    public function testRequestNotGeneratedIfAlreadyPresent()
    {
        $subjectUser = factory(User::class)->make(['id' => 1300005]);

        $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('find')
                ->andReturn(
                    User::initModelWithData([
                        'id' => 1300005,
                        'name_first' => 'Callum',
                    ])
                );
        })->makePartial();

        // create existing request.
        $this->endorsement->requests()->create([
            'user_id' => $subjectUser->id,
            'requested_by' => $subjectUser->id,
        ]);

        $this->graphQL("
        mutation {
            requestSpecialEndorsement (
                input: {
                    user_id: 1300005,
                    endorsement_id: {$this->endorsement->id},
                    requested_by: 1300002
                }
            ) {
                approved_at
                denied_at
                user {
                    name_first
                }
            }
        }
        ")->assertJsonPath('errors.0.message', 'A request is already present for that user on the given endorsement')
            ->assertJsonPath('errors.0.extensions.code', 422);
    }

    /** @test */
    public function testRequestCanBeActioned()
    {
        $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('find')
                ->andReturn(
                    User::initModelWithData([
                        'id' => 1300005,
                        'name_first' => 'Callum',
                    ])
                );

            $mock->shouldReceive('findMany')
                ->andReturn(
                    collect([
                        User::initModelWithData([
                            'id' => 1300005,
                            'name_first' => 'Callum',
                        ]),
                    ])
                );

            $mock->shouldReceive('findOrFail')
                ->andReturn(
                    User::initModelWithData([
                        'id' => 1300005,
                        'name_first' => 'Callum',
                    ])
                );
        })->makePartial();

        $request = $this->endorsement->requests()->create([
            'user_id' => 1300005,
            'requested_by' => 1300005,
        ]);

        $this->graphQL("
        mutation {
            grantSpecialEndorsement(request_id: {$request->id}, actioner_id: 1300005) {
                user {
                    name_first
                }
                endorsement_id
            }
        }
        ")->assertJsonPath('data.grantSpecialEndorsement.user.name_first', 'Callum');
    }
}
