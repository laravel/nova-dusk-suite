<?php

namespace App\Nova\Dependents;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Http\Requests\NovaRequest;

class CompanyDescription
{
    /**
     * The dependent name.
     *
     * @var string
     */
    protected $name;

    /**
     * Company description dependents.
     *
     * @param  string  $name
     */
    public function __construct($name = 'name')
    {
        $this->name = $name;
    }

    /**
     * Handle dependent field.
     *
     * @param  \Laravel\Nova\Fields\Field  $field
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Nova\Fields\FormData  $formData
     * @return void
     */
    public function __invoke(Field $field, NovaRequest $request, FormData $formData)
    {
        $name = $formData->get($this->name);

        if (! empty($name)) {
            $field->show();
        }

        $field->default(
            ! in_array($name, ['Laravel LLC', 'Tailwind Labs Inc'])
                ? "{$name}'s Description"
                : ''
        );
    }
}
