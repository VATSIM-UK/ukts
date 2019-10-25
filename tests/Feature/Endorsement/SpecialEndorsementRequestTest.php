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
        $this->mock(User::class, function ($mock) {
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
}
