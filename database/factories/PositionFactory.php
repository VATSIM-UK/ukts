<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Modules\Airfield\Airfield;
use App\Modules\Position\Position;
use Faker\Generator as Faker;

$factory->define(Position::class, function (Faker $faker) {
    return [
        'callsign' => strtoupper($faker->lexify('????')).'_'.$faker->randomElement([
                'TWR', 'GND', 'DEL', 'APP', 'ATIS', 'CTR',
            ]),
        'name' => ucfirst($faker->word).' '.$faker->randomElement([
                'Tower', 'Ground', 'Delivery', 'Approach', 'Information', 'Control',
            ]),
        'frequency' => $faker->randomFloat(3, 0, 130),
        'type' => $faker->numberBetween(1, 8),
        'airfield' => factory(Airfield::class)
    ];
});
