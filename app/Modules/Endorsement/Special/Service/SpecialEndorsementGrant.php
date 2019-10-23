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
        if ($this->specialEndorsement->users()->hasByID($this->user)) {
            throw new EndorsementAlreadyGrantedException();
        }


        // check if a request already exists for the given user / endorsement combination
        tap($this->specialEndorsement->requests()->where(
            [
                'user_id' => $this->user->id,
                'endorsement_id' => $this->specialEndorsement->id
            ]
        )->count() > 0,
            function ($requestExists) {
                if ($requestExists) {
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
