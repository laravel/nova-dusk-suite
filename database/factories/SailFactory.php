<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Sail::class, function (Faker $faker) {
    return [
        'ship_id' => factory(App\Ship::class),
        'inches' => random_int(50, 100),
    ];
});
