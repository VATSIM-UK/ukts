<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Modules\Position\TrainingPosition;
use Faker\Generator as Faker;

$factory->define(TrainingPosition::class, function (Faker $faker) {
    return [
        'position_id' => factory(App\Modules\Position\Position::class),
    ];
});
