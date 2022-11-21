<?php

namespace Database\Factories;

use App\Models\Flight;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template TModel of \App\Models\Flight
 */
class FlightFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = Flight::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
        ];
    }
}
