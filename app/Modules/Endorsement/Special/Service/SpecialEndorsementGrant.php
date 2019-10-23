<?php


namespace App\Modules\Endorsement\Special\Service;


use App\Modules\Endorsement\Special\Exceptions\EndorsementAlreadyGrantedException;
use App\Modules\Endorsement\Special\Exceptions\EndorsementRequestAlreadyExistsException;
use App\Modules\Endorsement\Special\SpecialEndorsement;
use App\User;

class SpecialEndorsementGrant
{
    private $user;
    private $requester;
    /** @var SpecialEndorsement */
    private $specialEndorsement;

    public function __construct(SpecialEndorsement $specialEndorsement, User $user, User $requester)
    {
        $this->specialEndorsement = $specialEndorsement;
        $this->user = $user;
        $this->requester = $requester;
    }

    public function handle()
    {
        if ($this->specialEndorsement->users()->find($this->user) == null) {
            throw new EndorsementAlreadyGrantedException();
        }

        dd('here');

        // check if a request already exists for the given user / endorsement combination
        tap($this->specialEndorsement->requests()->get()->where(
            [
                'user_id' => $this->user->id,
                'endorsement_id' => $this->specialEndorsement->id
            ]
        ),
            function ($request) {
                if ($request != null) {
                    throw new EndorsementRequestAlreadyExistsException();
                }
            }
        );

        // file new pending request for a special endorsement
        return $this->specialEndorsement->requests()->create([
            'user_id' => $this->user->id,
            'requested_by' => $this->requester->id
        ])->load(['user', 'endorsement', 'requester', 'actioner']);
    }
}
