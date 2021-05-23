<?php

namespace Database\Factories;

use App\Models\Dock;
use Illuminate\Database\Eloquent\Factories\Factory;

class DockFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Dock::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
        ];
    }
}
