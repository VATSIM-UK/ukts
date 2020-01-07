<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Modules\Position\TrainingPositionAssignment;
use Faker\Generator as Faker;

$factory->define(TrainingPositionAssignment::class, function (Faker $faker) {
    return [
        'position_id' => factory(App\Modules\Position\Position::class)
    ];
});
