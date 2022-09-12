<?php

namespace App\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @template TModel of \App\Models\Ship
 * @extends \App\Nova\Resource<TModel>
 */
class Ship extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<TModel>
     */
    public static $model = 'App\Models\Ship';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name',
    ];

    public static $relatableSearchResults = 5;

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make('ID', 'id')->sortable(),
            BelongsTo::make('Dock', 'dock')->display('name')->searchable(),
            Text::make('Name', 'name')->rules('required')->sortable(),

            DateTime::make('Departed At', 'departed_at'),

            BelongsToMany::make('Captains', 'captains')
                        ->display('name')
                        ->fields(function ($request) {
                            return [
                                Text::make('Notes', 'notes')->rules('max:20'),
                                File::make('Contract', 'contract')->prunable()->store(function ($request) {
                                    if ($request->contract) {
                                        return $request->contract->storeAs('/', 'Contract.pdf', 'public');
                                    }
                                }),
                            ];
                        })
                        ->prunable()
                        ->searchable(uses_searchable()),

            HasMany::make('Sails', 'sails', Sail::class),
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
        return [
            new Actions\MarkAsActive,
        ];
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
}
