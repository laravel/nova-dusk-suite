<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\People;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Photo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'imageable_type' => Company::class,
            'imageable_id' => CompanyFactory::new(),
        ];
    }

    /**
     * Indicate that the model's should be assigned to company
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function forCompany()
    {
        return $this->state(function (array $attributes) {
            return [
                'imageable_type' => Company::class,
                'imageable_id' => CompanyFactory::new(),
            ];
        });
    }

    /**
     * Indicate that the model's should be assigned to company
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function forPerson()
    {
        return $this->state(function (array $attributes) {
            return [
                'imageable_type' => People::class,
                'imageable_id' => PeopleFactory::new(),
            ];
        });
    }
}
