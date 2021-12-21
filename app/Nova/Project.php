<?php

namespace App\Nova;

use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Trix;
use Laravel\Nova\Http\Requests\NovaRequest;

class Project extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Project::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $productTypes = [
            'product' => 'Product',
            'service' => 'Service',
        ];

        return [
            ID::make(__('ID'), 'id')->sortable(),

            Text::make('Name'),

            Trix::make('Description')->nullable(),

            Select::make('Type')->options($productTypes)->displayUsing(function ($value) use ($productTypes) {
                return $productTypes[$value] ?? null;
            })->dependsOn('name', function ($field, $request, $formData) use ($productTypes) {
                if (in_array($formData->name, ['Nova', 'Spark'])) {
                    $field->options(collect($productTypes)->filter(function ($title, $type) {
                        return $type === 'product';
                    }))->default('product');
                } elseif (in_array($formData->name, ['Forge', 'Envoyer', 'Vapor'])) {
                    $field->options(collect($productTypes)->filter(function ($title, $type) {
                        return $type === 'service';
                    }))->default('service');
                }
            })->nullable()->rules(['nullable', Rule::in(array_keys($productTypes))]),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }
}
