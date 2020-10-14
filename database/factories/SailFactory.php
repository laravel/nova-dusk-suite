<?php

namespace Database\Factories;

use App\Models\Sail;
use Illuminate\Database\Eloquent\Factories\Factory;

class SailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sail::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'ship_id' => ShipFactory::new()->create(),
            'inches' => random_int(50, 100),
        ];
    }
}
