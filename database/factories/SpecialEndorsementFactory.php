<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Modules\Endorsement\Special\SpecialEndorsement;
use Faker\Generator as Faker;

$factory->define(SpecialEndorsement::class, function (Faker $faker) {
    return [
        'name' => $faker->word
    ];
});
