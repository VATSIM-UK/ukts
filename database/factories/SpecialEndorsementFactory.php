<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\SpecialEndorsement;
use Faker\Generator as Faker;

$factory->define(SpecialEndorsement::class, function (Faker $faker) {
    return [
        'name' => $faker->word
    ];
});
