<?php

namespace App\Nova;

use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @template TModel of \App\Models\Role
 *
 * @extends \App\Nova\Resource<TModel>
 */
class Role extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<TModel>
     */
    public static $model = \App\Models\Role::class;

    /**
     * The columns that should be searched.
     *
     * @var array<int, string>
     */
    public static $search = [
        'id', 'name',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make('ID', 'id')->sortable(),
            Text::make('Name', 'name')->rules('required')->sortable(),

            $this->mergeWhen(! $request->viaManyToMany(), function () {
                return [
                    DateTime::make('Created At')->exceptOnForms(),
                    DateTime::make('Updated At')->exceptOnForms(),
                ];
            }),

            BelongsToMany::make('Users', 'users')
                ->display('name')
                ->fields(function ($request) {
                    return [
                        Text::make('Notes', 'notes')->rules('max:20'),
                    ];
                })
                ->actions(function ($request) {
                    return [
                        new Actions\UpdatePivotNotes,
                        new Actions\UpdateRequiredPivotNotes,
                    ];
                })
                ->searchable(uses_searchable())
                ->prunable()
                ->filterable()
                ->reorderAttachables(uses_with_reordering()),
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
}
