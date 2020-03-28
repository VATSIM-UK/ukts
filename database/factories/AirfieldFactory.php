<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Modules\Airfield\Airfield;
use Faker\Generator as Faker;

$factory->define(Airfield::class, function (Faker $faker) {
    return [
        'icao' => (string) $faker->lexify('????'),
        'iata' => $faker->lexify('???'),
        'display_name' => $faker->city,
        'airfield_group_id' => null
    ];
});
