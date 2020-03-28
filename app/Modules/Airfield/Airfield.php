<?php

namespace App\Modules\Airfield;

use App\Modules\AirfieldGroup\AirfieldGroup;
use App\Modules\Position\Position;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airfield extends Model
{
    protected $keyType = 'string';
    protected $primaryKey = 'icao';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = ['icao', 'display_name', 'iata', 'airfield_group_id'];
    protected $casts = ['icao' => 'string'];

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class, 'airfield_icao', 'icao');
    }

    /** @test */
    public function airfieldGroup(): BelongsTo
    {
        return $this->belongsTo(AirfieldGroup::class);
    }
}
