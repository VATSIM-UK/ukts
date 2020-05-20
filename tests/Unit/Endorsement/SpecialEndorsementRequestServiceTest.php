<?php

namespace Tests\Feature\Unit\Endorsement;

use App\Modules\Endorsement\Special\Assignment;
use App\Modules\Endorsement\Special\Exceptions\EndorsementAlreadyGrantedException;
use App\Modules\Endorsement\Special\Exceptions\EndorsementRequestAlreadyExistsException;
use App\Modules\Endorsement\Special\Services\SpecialEndorsementAssignment;
use App\Modules\Endorsement\Special\Services\SpecialEndorsementRequest;
use App\Modules\Endorsement\Special\SpecialEndorsement;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpecialEndorsementRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $specialEndorsement;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->make();
        $this->specialEndorsement = factory(SpecialEndorsement::class)->create();
    }

    /** @test */
    public function itSuccessfullyFilesRequestsForEndorsementsWithValidParameters()
    {
        (new SpecialEndorsementRequest($this->specialEndorsement, $this->user, $this->user))->handle();

        $this->assertDatabaseHas('endorsement_requests', [
            'endorsement_id' => $this->specialEndorsement->id,
            'user_id' => $this->user->id,
            'requested_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function itWillThrowAnExceptionIfRequestAlreadyExists()
    {
        // create existing request.
        $this->specialEndorsement->requests()->create([
            'user_id' => $this->user->id,
            'requested_by' => $this->user->id,
        ]);

        $this->expectException(EndorsementRequestAlreadyExistsException::class);

        (new SpecialEndorsementRequest($this->specialEndorsement, $this->user, $this->user))->handle();

        $this->assertDatabaseMissing('endorsement_requests', [
            'endorsement_id' => $this->specialEndorsement->id,
            'user_id' => $this->user->id,
            'requested_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function itWillThrowAnExceptionIfEndorsementHasAlreadyBeenGranted()
    {
        // creates the assignment of the endorsement.
        Assignment::create([
            'endorsement_id' => $this->specialEndorsement->id,
            'user_id' => $this->user->id,
            'granted_by' => $this->user->id,
        ]);

        $this->expectException(EndorsementAlreadyGrantedException::class);

        (new SpecialEndorsementRequest($this->specialEndorsement, $this->user, $this->user))->handle();

        $this->assertDatabaseMissing('endorsement_requests', [
            'endorsement_id' => $this->specialEndorsement->id,
            'user_id' => $this->user->id,
            'requested_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function itCanActionRequestsForSpecialEndorsements()
    {
        $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('find')
                ->andReturn(
                    User::initModelWithData([
                        'id' => 1300005,
                        'name_first' => 'Callum',
                    ])
                );

            $mock->shouldReceive('initModelWithData')
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

        $request = $this->specialEndorsement->requests()->create([
            'user_id' => 1300005,
            'requested_by' => 1300002,
        ]);

        (new SpecialEndorsementAssignment($request, $this->user))->handle();

        $this->assertDatabaseHas('special_endorsement_assignments', [
            'user_id' => 1300005,
            'endorsement_id' => $this->specialEndorsement->id,
            'granted_by' => $this->user->id,
        ]);
    }
}
