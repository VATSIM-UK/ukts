<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Modules\Availability\Availability;
use Faker\Generator as Faker;

$factory->define(Availability::class, function (Faker $faker) {
    $from = $faker->dateTimeThisYear;
    $to = $faker->dateTimeInInterval($from, '+1 day');

    return [
        'user_id' => $faker->date(1000000, 9999999),
        'from' => $from,
        'to' => $to,
    ];
});
