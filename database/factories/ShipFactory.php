<?php

namespace Database\Factories;

use App\Models\Ship;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template TModel of \App\Models\Ship
 */
class ShipFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = Ship::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'dock_id' => DockFactory::new(),
            'name' => $this->faker->word(),
        ];
    }
}
