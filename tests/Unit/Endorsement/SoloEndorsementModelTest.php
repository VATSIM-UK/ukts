<?php

namespace Tests\Unit\Endorsement;

use App\Modules\Endorsement\Solo\SoloEndorsement;
use App\Modules\Position\Position;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoloEndorsementModelTest extends TestCase
{
    use RefreshDatabase;

    private $position;

    public function setUp(): void
    {
        parent::setUp();

        $this->position = factory(Position::class)->create();
    }

    /** @test */
    public function itDeterminesActiveSoloEndorsementsByReturningOnlyThoseEndorsementsWhichHaveNotExpired()
    {
        $user = User::initModelWithData(['id' => 1234567]);
        $expiredEndorsement = SoloEndorsement::create([
            'position_id' => $this->position->id,
            'user_id' => $user->id,
            'expiry_date' => Carbon::now()->subHours(1),
        ]);

        $activeEndorsement = SoloEndorsement::create([
            'position_id' => $this->position->id,
            'user_id' => $user->id,
            'expiry_date' => Carbon::now()->addHours(24),
        ]);

        $collectionResult = SoloEndorsement::active()->get();

        $this->assertTrue($collectionResult->contains($activeEndorsement));
        $this->assertFalse($collectionResult->contains($expiredEndorsement));
    }
}
