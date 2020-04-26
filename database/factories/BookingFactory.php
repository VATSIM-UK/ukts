<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Modules\Bookings\Booking;
use Faker\Generator as Faker;

$factory->define(Booking::class, function (Faker $faker) {
    $from = $faker->dateTimeThisYear;
    $to = $faker->dateTimeInInterval($from, '+1 day');

    return [
        'user_id' => $faker->numberBetween(1000000, 9999999),
        'position_id' => factory(\App\Modules\Position\Position::class),
        'network_type' => 0,
        'from' => $from,
        'to' => $to,
    ];
});
