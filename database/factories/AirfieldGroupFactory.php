<?php

/** @var Illuminate\Database\Eloquent\Factory $factory */

use App\Modules\AirfieldGroup\AirfieldGroup;
use Faker\Generator as Faker;

$factory->define(AirfieldGroup::class, function (Faker $faker) {
    return [
        'name' => $faker->word
    ];
});
