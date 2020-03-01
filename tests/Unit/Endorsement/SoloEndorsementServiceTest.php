<?php

namespace Tests\Unit;

use App\Modules\Endorsement\Solo\SoloEndorsement;
use App\Modules\Endorsement\Solo\SoloEndorsementAlreadyGrantedException;
use App\Modules\Endorsement\Solo\SoloEndorsementService;
use App\Modules\Position\Position;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoloEndorsementServiceTest extends TestCase
{
    use RefreshDatabase;

    private $service;
    private $position;
    private $mockUserId = 1234567;
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(SoloEndorsementService::class);

        $this->position = factory(Position::class)->create();

        $this->user = User::initModelWithData(['id' => $this->mockUserId]);
    }

    /** @test */
    public function itShouldCreateSoloEndorsementAgainstPositionAndUser()
    {
        $this->service->grantSoloEndorsement($this->position, $this->user);

        $this->assertDatabaseHas('solo_endorsements', [
            'position_id' => $this->position->id,
            'user_id' => $this->user->id,
            'expiry_date' => Carbon::now()->addDays(30)
        ]);
    }

    /** @test */
    public function itShouldNotGrantSoloEndorsementIfAlreadyAssignedToUser()
    {
        $this->expectException(SoloEndorsementAlreadyGrantedException::class);

        SoloEndorsement::create([
            'position_id' => $this->position->id,
            'user_id' => $this->user->id,
            'expiry_date' => Carbon::now()->addDays(30)
        ]);

        $this->service->grantSoloEndorsement($this->position, $this->user);
    }

    /** @test */
    public function itCorrectlyDeterminesIfSoloEndorsementIsCurrentlyActive()
    {
        SoloEndorsement::create([
            'position_id' => $this->position->id,
            'user_id' => $this->user->id,
            'expiry_date' => Carbon::now()->addDays(30)
        ]);

        $result = $this->service->checkForExistingActiveSoloEndorsements($this->position, $this->user);

        $this->assertTrue($result);
    }

    /** @test */
    public function itCorrectlyDeterminesIfSoloEndorsementHasExpired()
    {
        $user = User::initModelWithData(['id' => $this->mockUserId]);
        SoloEndorsement::create([
            'position_id' => $this->position->id,
            'user_id' => $user->id,
            'expiry_date' => Carbon::now()->subDays(1)
        ]);

        $result = $this->service->checkForExistingActiveSoloEndorsements($this->position, $this->user);

        $this->assertFalse($result);
    }
}
