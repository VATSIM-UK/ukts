<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Modules\Endorsement\Solo\SoloEndorsement;
use Faker\Generator as Faker;

$factory->define(SoloEndorsement::class, function (Faker $faker) {
    return [
        'position_id' => factory(\App\Modules\Endorsement\Solo\SoloEndorsement::class),
        'user_id' => factory(\App\User::class),
        'expiry_date' => \Carbon\Carbon::now()->addDays(30),
    ];
});
