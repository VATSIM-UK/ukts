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

    protected function setUp(): void
    {
        parent::setUp();

        $this->endorsement = factory(SpecialEndorsement::class)->create();
    }

    /** @test */
    public function testRequestCanBeMadeAgainstAnEndorsement()
    {
        $this->mockUserResolvedValues();

        $this->graphQL("
        mutation {
            requestSpecialEndorsement (user_id: 1300005, endorsement_id: {$this->endorsement->id}, requested_by: 1300002) {
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
        $this->mockUserResolvedValues();

        $subjectUser = factory(User::class)->make(['id' => 1300005]);

        // create existing request.
        $this->endorsement->requests()->create([
            'user_id' => $subjectUser->id,
            'requested_by' => $subjectUser->id
        ]);

        $this->graphQL("
        mutation {
            requestSpecialEndorsement (user_id: 1300005, endorsement_id: {$this->endorsement->id}, requested_by: 1300002) {
                approved_at
                denied_at
                user {
                    name_first
                }
            }
        }
        ")->assertJsonPath('errors.0.message', "A request is already present for that user on the given endorsement")
            ->assertJsonPath('errors.0.extensions.code', 422);
    }

    private function mockUserResolvedValues()
    {
        return $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('find')
                ->andReturn(
                    User::initModelWithData([
                        'id' => 1300005,
                        'name_first' => 'Callum'
                    ])
                );

            $mock->shouldReceive('find')
                ->andReturn(
                    User::initModelWithData([
                        'id' => 1300002,
                        'name_first' => 'Alex'
                    ])
                );

            $mock->shouldReceive('find')
                ->andReturn(
                    User::initModelWithData([
                        'id' => 1300005,
                        'name_first' => 'Callum'
                    ])
                );
        })->makePartial();
    }
}
