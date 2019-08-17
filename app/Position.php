<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['callsign', 'name', 'frequency', 'type'];

    const TYPE_ATIS = 1;
    const TYPE_DELIVERY = 2;
    const TYPE_GROUND = 3;
    const TYPE_TOWER = 4;
    const TYPE_APPROACH = 5;
    const TYPE_ENROUTE = 6;
    const TYPE_TERMINAL = 7;
    const TYPE_FSS = 8;

    const types = [
        self::TYPE_ATIS => ['ATIS', 'ATIS'],
        self::TYPE_DELIVERY => ['DEL', 'Delivery'],
        self::TYPE_GROUND => ['GND', 'Ground'],
        self::TYPE_TOWER => ['TWR', 'Tower'],
        self::TYPE_APPROACH => ['APP', 'Approach'],
        self::TYPE_ENROUTE => ['CTR', 'Enroute'],
        self::TYPE_TERMINAL => ['TMA', 'Terminal'],
        self::TYPE_FSS => ['FSS', 'Flight Service Station'],
    ];

    public function getTypeAttribute()
    {
        return self::types[$this->attributes['type']][0];
    }

    public function getTypeHumanAttribute()
    {
        return self::types[$this->attributes['type']][1];
    }
}
