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
 *
 * @extends \App\Nova\Resource<TModel>
 */
class Ship extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<TModel>
     */
    public static $model = \App\Models\Ship::class;

    /**
     * The columns that should be searched.
     *
     * @var array<int, string>
     */
    public static $search = [
        'id', 'name',
    ];

    /**
     * The number of results to display when searching relatable resource without Scout.
     *
     * @var int
     */
    public static $relatableSearchResults = 5;

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make('ID', 'id')->sortable(),
            BelongsTo::make('Dock', 'dock')->display('name')->searchable()->peekable()->showWhenPeeking(),
            Text::make('Name', 'name')->rules('required')->sortable()->showWhenPeeking(),

            DateTime::make('Departed At', 'departed_at')->showWhenPeeking(),

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
                ->filterable()
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
    public function cards(NovaRequest $request): array
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
    public function filters(NovaRequest $request): array
    {
        return [];
    }
}
