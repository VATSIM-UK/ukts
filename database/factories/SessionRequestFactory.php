<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Modules\Bookings\Booking;
use App\Modules\Position\Position;
use App\Modules\SessionRequest\SessionRequest;
use Faker\Generator as Faker;

$factory->define(SessionRequest::class, function (Faker $faker) {
    return [
        'user_id' => 12345,
        'position_id' => factory(Position::class),
        'booking_id' => factory(Booking::class),
        'cancelled_at' => null,
    ];
});
