<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Booking\Booking;
use App\Modules\Endorsement\Special\Assignment;
use App\Modules\Endorsement\Special\SpecialEndorsement;
use VATSIMUK\Support\Auth\Models\RemoteUser;

class User extends RemoteUser
{
    public function specialEndorsements(): BelongsToMany
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
}
