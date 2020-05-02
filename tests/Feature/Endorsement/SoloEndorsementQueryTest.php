<?php

namespace Tests\Feature\Endorsement;

use App\Modules\Endorsement\Solo\SoloEndorsement;
use App\Modules\Position\Position;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class SoloEndorsementQueryTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests;

    private $position;
    private $mockUserId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->position = factory(Position::class)->create();

        $this->mockUserId = 1112220;
    }

    /** @test */
    public function testSoloEndorsementCanBeGrantedViaMutation()
    {
        $this->mockRemoteUserCall($this->mockUserId);

        $this->grantSoloEndorsementQuery()
            ->assertJson(['data.grantSoloEndorsement.user.id', $this->mockUserId])
            ->assertJsonPath('data.grantSoloEndorsement.position.callsign', $this->position->callsign);
    }

    /** @test */
    public function testSoloEndorsementIsntCreatedIfAlreadyActive()
    {
        factory(SoloEndorsement::class)->create(['position_id' => $this->position->id, 'user_id' => $this->mockUserId]);

        $this->mockRemoteUserCall($this->mockUserId);

        $this->grantSoloEndorsementQuery()
            ->assertJsonPath('errors.0.message',
                'A solo endorsement is already assigned to that user on the given position');
    }

    private function grantSoloEndorsementQuery(): TestResponse
    {
        return $this->graphQL("
        mutation {
            grantSoloEndorsement (
                input: {
                    position_id: {$this->position->id}
                    user_id: {$this->mockUserId}
                }
            ) {
                id
                user {
                    id
                }
                position {
                    callsign
                }
            }
        }");
    }

    private function mockRemoteUserCall(int $userId): void
    {
        $this->userId = $userId;
        $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('findOrFail')
                ->andReturn(
                    new User([
                        'id' => $this->userId,
                        'name_first' => 'Callum',
                    ])
                );
        })->makePartial();
    }
}
