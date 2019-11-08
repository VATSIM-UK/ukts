<?php

namespace App\Modules\Endorsement\Special\Services;

use App\Contracts\BaseService;
use App\Modules\Endorsement\Special\Exceptions\EndorsementAlreadyGrantedException;
use App\Modules\Endorsement\Special\Exceptions\EndorsementRequestAlreadyExistsException;
use App\Modules\Endorsement\Special\SpecialEndorsement;
use App\User;

class SpecialEndorsementRequest implements BaseService
{
    private $specialEndorsement;
    private $requester;
    private $user;

    public function __construct(SpecialEndorsement $specialEndorsement, User $user, User $requester)
    {
        $this->specialEndorsement = $specialEndorsement;
        $this->user = $user;
        $this->requester = $requester;
    }

    public function handle()
    {
        // check if the endorsement has already been granted.
        if ($this->specialEndorsement->users()->hasByID($this->user)) {
            throw new EndorsementAlreadyGrantedException();
        }

        // check if a request already exists for the given user / endorsement combination
        if ($this->specialEndorsement->requests()->where(
            [
                'user_id' => $this->user->id,
                'endorsement_id' => $this->specialEndorsement->id
            ]
        )->get()->isNotEmpty()) {
            throw new EndorsementRequestAlreadyExistsException();
        }

        // file new pending request for a special endorsement
        return $this->specialEndorsement->requests()->create([
            'user_id' => $this->user->id,
            'requested_by' => $this->requester->id
        ]);
    }
}
