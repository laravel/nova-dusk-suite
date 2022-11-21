<?php

namespace Database\Factories;

use App\Models\Captain;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template TModel of \App\Models\Captain
 */
class CaptainFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = Captain::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'photo' => '',
        ];
    }
}
