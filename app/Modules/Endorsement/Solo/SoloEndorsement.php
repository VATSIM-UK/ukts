<?php

namespace App\Modules\Endorsement\Solo;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SoloEndorsement extends Model
{
    protected $fillable = ['user_id', 'position_id', 'expiry_date'];

    protected $dates = ['expiry_date'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereDate('expiry_date', '>', Carbon::now());
    }
}
