<?php

namespace App\Modules\Availability;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
//use VATSIMUK\Auth\Remote\RemoteEloquent\HasCustomInstanceCreation;
use VATSIMUK\Auth\Remote\RemoteEloquent\HasRemoteRelationships;

class Availability extends Model
{
    use HasRemoteRelationships;

    protected $table = 'availability';
    protected $fillable = ['user_id', 'from', 'to'];
    protected $dates = ['from', 'to'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
