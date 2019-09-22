<?php

namespace App;

use VATSIMUK\Auth\Remote\Models\RemoteUser;

class User extends RemoteUser
{
    /*
     * Per-Service Relationships
     */

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
