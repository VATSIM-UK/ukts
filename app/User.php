<?php

namespace App;

use App\Modules\Endorsement\Special\Assignment;
use App\Modules\Endorsement\Special\SpecialEndorsement;
use VATSIMUK\Auth\Remote\Models\RemoteUser;

class User extends RemoteUser
{
    public function specialEndorsements()
    {
        return $this->belongsToMany(
            SpecialEndorsement::class,
            'special_endorsement_assignments',
            'user_id',
            'endorsement_id'
        )->using(Assignment::class)->withTimestamps();
    }
}
