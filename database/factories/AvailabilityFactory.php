<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Modules\Availability\Availability;
use App\User;
use Faker\Generator as Faker;

$factory->define(Availability::class, function (Faker $faker) {
    $from = $faker->dateTimeThisYear;
    $to = $faker->dateTimeInInterval($from, '+1 day');

    return [
        'user_id' => factory(User::class),
        'from' => $from,
        'to' => $to,
    ];
});
