<?php

namespace App\Modules\Airfield;

use App\Modules\Position\Position;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airfield extends Model
{
    protected $keyType = 'string';
    protected $primaryKey = 'icao';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = ['icao', 'display_name', 'iata'];
    protected $casts = ['icao' => 'string'];

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class, 'airfield_icao', 'icao');
    }
}
