<?php

namespace App\Modules\Position;

use App\Modules\Bookings\Booking;
use App\Modules\Endorsement\Special\SpecialEndorsement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    const TYPE_ATIS = 1;
    const TYPE_DELIVERY = 2;
    const TYPE_GROUND = 3;
    const TYPE_TOWER = 4;
    const TYPE_APPROACH = 5;
    const TYPE_ENROUTE = 6;
    const TYPE_TERMINAL = 7;
    const TYPE_FSS = 8;
    const TYPES = [
        self::TYPE_ATIS => ['ATIS', 'ATIS'],
        self::TYPE_DELIVERY => ['DEL', 'Delivery'],
        self::TYPE_GROUND => ['GND', 'Ground'],
        self::TYPE_TOWER => ['TWR', 'Tower'],
        self::TYPE_APPROACH => ['APP', 'Approach'],
        self::TYPE_ENROUTE => ['CTR', 'Enroute'],
        self::TYPE_TERMINAL => ['TMA', 'Terminal'],
        self::TYPE_FSS => ['FSS', 'Flight Service Station'],
    ];
    protected $fillable = ['callsign', 'name', 'frequency', 'type'];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function specialEndorsements(): BelongsToMany
    {
        return $this->belongsToMany(
            SpecialEndorsement::class,
            'special_endorsement_positions',
            'position_id',
            'endorsement_id'
        );
    }

    public function getTypeAttribute(): string
    {
        return self::TYPES[$this->attributes['type']][0];
    }

    public function getTypeHumanAttribute(): string
    {
        return self::TYPES[$this->attributes['type']][1];
    }

    public function getSuffixAttribute(): string
    {
        static $GROUP_1 = 1;
        $matches = [];
        preg_match('/_([A-Z]*+$)/', $this->attributes['callsign'], $matches);

        return $matches[$GROUP_1];
    }
}
