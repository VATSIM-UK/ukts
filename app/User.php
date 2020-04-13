<?php

namespace App;

use App\Modules\Availability\Availability;
use App\Modules\Bookings\Booking;
use App\Modules\Endorsement\Special\Assignment;
use App\Modules\Endorsement\Special\SpecialEndorsement;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use VATSIMUK\Auth\Remote\Models\RemoteUser;

class User extends RemoteUser implements Authenticatable
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

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function availability(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

}
