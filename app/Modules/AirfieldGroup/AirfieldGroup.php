<?php

namespace App\Modules\AirfieldGroup;

use App\Modules\Airfield\Airfield;
use App\Modules\Position\Position;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class AirfieldGroup extends Model
{
    public $timestamps = false;
    protected $fillable = ['name'];

    public function airfields(): HasMany
    {
        return $this->hasMany(Airfield::class);
    }

    public function positions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Position::class,
            Airfield::class,
            'airfield_group_id',
            'airfield_icao'
        );
    }
}
