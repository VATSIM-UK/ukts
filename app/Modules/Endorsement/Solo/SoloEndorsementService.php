<?php

namespace App\Modules\Endorsement\Solo;

use App\Modules\Position\Position;
use App\User;
use Carbon\Carbon;

class SoloEndorsementService
{
    private const DEFAULT_DURATION_IN_DAYS = 30;

    public function grantSoloEndorsement(Position $position, User $user): SoloEndorsement
    {
        throw_if(
            $this->checkForExistingActiveSoloEndorsements($position, $user),
            SoloEndorsementAlreadyGrantedException::class
        );

        return SoloEndorsement::create([
            'position_id' => $position->id,
            'user_id' => $user->id,
            'expiry_date' => Carbon::now()->addDays(self::DEFAULT_DURATION_IN_DAYS)
        ]);
    }

    public function checkForExistingActiveSoloEndorsements(Position $position, User $user): bool
    {
        $queryResult = SoloEndorsement::active()->where([
            'position_id' => $position->id,
            'user_id' => $user->id
        ])->get();

        return ! $queryResult->isEmpty();
    }
}
