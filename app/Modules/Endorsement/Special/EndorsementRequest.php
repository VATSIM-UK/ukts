<?php

namespace App\Modules\Endorsement\Special;

use App\Concerns\HasCustomInstanceCreation;
use App\User;
use Illuminate\Database\Eloquent\Model;

class EndorsementRequest extends Model
{
    use HasCustomInstanceCreation;

    protected $fillable = ['user_id', 'endorsement_id', 'requested_by'];

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
