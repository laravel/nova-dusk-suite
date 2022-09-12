<?php

namespace App\Nova;

use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\VaporImage;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @template TModel of \App\Models\Captain
 * @extends \App\Nova\Resource<TModel>
 */
class Captain extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<TModel>
     */
    public static $model = \App\Models\Captain::class;

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
        $user = $request->user();

        return [
            ID::make('ID', 'id')->sortable(),

            Text::make('Name', 'name')
                ->rules('required')
                ->sortable(),

            $this->merge(function () use ($user) {
                $storage = $user->settings['storage'] ?? 'local' === 'local';

                if ($storage === 's3') {
                    return [
                        VaporImage::make('Photo', 'photo')
                            ->prunable()
                            ->help('Using cloud storage'),
                    ];
                }

                return [
                    Image::make('Photo', 'photo')
                        ->prunable()
                        ->help('Using local storage'),
                ];
            }),

            BelongsToMany::make('Ships', 'ships')
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
            Actions\FieldsAction::make()->standalone(),
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
