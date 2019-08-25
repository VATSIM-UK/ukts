<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Booking;
use Faker\Generator as Faker;

$factory->define(Booking::class, function (Faker $faker) {
    $from = $faker->dateTimeThisYear;
    $to = $faker->dateTimeInInterval($from, '+1 day');
    return [
        'user_id' => $faker->numberBetween(1000000, 9999999),
        'position_id' => factory(\App\Position::class),
        'from' => $from,
        'to' => $to,
    ];
});
