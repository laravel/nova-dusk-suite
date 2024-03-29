<?php

namespace Database\Factories;

use App\Models\Sail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template TModel of \App\Models\Sail
 */
class SailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = Sail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'ship_id' => ShipFactory::new(),
            'inches' => random_int(50, 100),
        ];
    }
}
