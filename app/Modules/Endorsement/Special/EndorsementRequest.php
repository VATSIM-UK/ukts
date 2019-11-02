<?php

namespace App\Modules\Endorsement\Special;

use App\User;
use Illuminate\Database\Eloquent\Model;
use VATSIMUK\Auth\Remote\RemoteEloquent\HasCustomInstanceCreation;

class EndorsementRequest extends Model
{
    use HasCustomInstanceCreation;

    protected $fillable = ['user_id', 'endorsement_id', 'endorsement_type', 'requested_by', 'approved_at', 'denied_at'];
    public $timestamps = ['created_at', 'updated_at', 'approved_at', 'denied_at'];

    public function endorsement()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(resolve(User::class));
    }

    public function requester()
    {
        return $this->belongsTo(resolve(User::class), 'requested_by');
    }

    public function actioner()
    {
        return $this->belongsTo(resolve(User::class), 'actioned_by');
    }
}
