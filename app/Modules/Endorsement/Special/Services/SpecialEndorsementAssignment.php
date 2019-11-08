<?php

namespace App\Modules\Endorsement\Special\Services;

use App\Contracts\BaseService;
use App\Modules\Endorsement\Special\EndorsementRequest;
use App\Modules\Endorsement\Special\Exceptions\EndorsementAlreadyGrantedException;
use App\User;

class SpecialEndorsementAssignment implements BaseService
{
    private $endorsementRequest;
    private $user;
    private $actioner;

    public function __construct(EndorsementRequest $endorsement, User $actioner)
    {
        $this->endorsementRequest = $endorsement;
        $this->actioner = $actioner;
    }

    public function handle()
    {
        // check if the endorsement has already been assigned.
        if ($this->endorsementRequest->endorsement->users->contains($this->endorsementRequest->user)) {
            throw new EndorsementAlreadyGrantedException();
        }
        // add the user to the endorsement relating to the request
        $this->endorsementRequest->endorsement->users()->attach($this->endorsementRequest->user,
            [
                'granted_by' => $this->actioner->id
            ]
        );

        // add the actioner into the request history
        $this->endorsementRequest->actioner()->associate($this->actioner);
        // update the approved at date
        $this->endorsementRequest->update(['approved_at' => now()]);

        // return the assignment object.
        return $this->endorsementRequest->fresh()->endorsement->users->find($this->endorsementRequest->user->id)->pivot;
    }
}
