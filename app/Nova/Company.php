<?php

namespace App\Nova;

use Laravel\Nova\Fields\Country;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\MorphOne;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @template TModel of \App\Models\Company
 *
 * @extends \App\Nova\Resource<TModel>
 */
class Company extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<TModel>
     */
    public static $model = \App\Models\Company::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array<int, string>
     */
    public static $search = [
        'name',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),

            Text::make('Name')
                ->rules('required')
                ->suggestions(['Laravel LLC', 'Tailwind Labs Inc']),

            Markdown::make('Description')
                ->dependsOn('name', Dependents\CompanyDescription::class)
                ->withFiles()
                ->hide()
                ->nullable(),

            Country::make('Country')
                ->dependsOn('name', function (Country $field, NovaRequest $request, FormData $formData) {
                    if ($formData->name !== 'Laravel LLC') {
                        $field->readonly(false);
                    }
                })
                ->readonly()
                ->nullable(),

            MorphOne::make('Photo'),

            HasMany::make('Profiles'),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Return the location to redirect the user after deletion.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return string
     */
    public static function redirectAfterDelete(NovaRequest $request)
    {
        return '/';
    }
}
