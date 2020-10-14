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

$factory->define(App\Address::class, function (Faker $faker) {
    return [
        'address_line_1' => $faker->word,
        'address_line_2' => $faker->word,
        'city' => $faker->word,
        'state' => $faker->word,
        'postal_code' => $faker->word,
        'country' => $faker->word,
    ];
});
