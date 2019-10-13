<?php

namespace App\Modules\Endorsement\Special;

use App\Position;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use VATSIMUK\Auth\Remote\RemoteEloquent\HasRemoteRelationships;

class SpecialEndorsement extends Model
{
    use SoftDeletes, HasRemoteRelationships;

    protected $fillable = ['name'];

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(
            Position::class,
            'special_endorsement_positions',
            'endorsement_id',
            'position_id'
        );
    }

    public function users(): BelongsToMany
    {
        return $this->remoteBelongsToMany(
            User::class,
            'special_endorsement_assignments',
            'endorsement_id'
        )->using(Assignment::class);
    }
}
